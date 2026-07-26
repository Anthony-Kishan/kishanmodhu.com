<?php

declare(strict_types=1);

/**
 * Structural and unit checks: config, the content-type registry against the
 * schema, validation rules, routing, helpers and request parsing.
 *
 * Loaded by tests/run.php, which has already bootstrapped the application.
 */

use App\Core\Config;
use App\Core\ContentType;
use App\Core\Model;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use Tests\Harness;

$root   = dirname(__DIR__);
$schema = (string) file_get_contents($root . '/database/schema.sql');
$seed   = (string) file_get_contents($root . '/database/seed.php');

// ── Config ──────────────────────────────────────────────────────────────────
Harness::group('config');
Harness::check('app config loads', is_string(Config::get('app.url')));
Harness::check('content types load', is_array(Config::get('content_types')));
Harness::check('settings groups load', Config::get('settings') !== []);
Harness::check('upload path is inside public/', str_starts_with(
    (string) Config::get('app.upload_path'),
    (string) Config::get('app.public_path')
));

// ── Content-type registry ↔ models ↔ schema ─────────────────────────────────
Harness::group('content type registry');

foreach (ContentType::all() as $type) {
    $model = $type->model();
    $key   = $type->key;

    Harness::check("{$key}: model instantiates", $model instanceof Model);

    $table = $model->table();
    Harness::check("{$key}: table declared in schema", str_contains($schema, "CREATE TABLE IF NOT EXISTS `{$table}`"));

    // A registered field that is not fillable would be silently dropped on save.
    $property = (new ReflectionClass($model))->getProperty('fillable');
    $property->setAccessible(true);
    /** @var array<int, string> $fillable */
    $fillable = $property->getValue($model);

    $notFillable = array_diff(array_keys($type->fields()), $fillable);
    Harness::check("{$key}: every field is fillable", $notFillable === [], implode(', ', $notFillable));

    // …and a field with no column would fail at insert time.
    preg_match('/CREATE TABLE IF NOT EXISTS `' . $table . '` \((.*?)\n\) ENGINE/s', $schema, $matches);
    $ddl = $matches[1] ?? '';

    $noColumn = array_filter(
        array_keys($type->fields()),
        static fn (string $field): bool => !str_contains($ddl, "`{$field}`")
    );
    Harness::check("{$key}: every field has a column", $noColumn === [], implode(', ', $noColumn));

    Harness::check("{$key}: list columns are real fields",
        array_diff($type->listColumns(), array_keys($type->fields())) === []);

    if ($type->isSortable()) {
        Harness::check("{$key}: sortable table has sort_order", str_contains($ddl, '`sort_order`'));
    }

    if ($type->isPublishable()) {
        Harness::check("{$key}: publishable table has is_published", str_contains($ddl, '`is_published`'));
    }

    foreach ($type->fields() as $name => $field) {
        Harness::check("{$key}.{$name}: has a label", ($field['label'] ?? '') !== '');
        Harness::check("{$key}.{$name}: type is supported", in_array(
            $field['type'] ?? '',
            ['text', 'textarea', 'number', 'url', 'email', 'select', 'boolean', 'image', 'list', 'file'],
            true
        ), $field['type'] ?? '(none)');
    }
}

// ── Settings registry ↔ seeder ──────────────────────────────────────────────
Harness::group('settings registry');

foreach (Config::get('settings') as $groupKey => $group) {
    Harness::check("{$groupKey}: has a label", ($group['label'] ?? '') !== '');

    foreach (array_keys($group['fields']) as $settingKey) {
        // An unseeded setting renders as an empty string on the live site.
        Harness::check("{$settingKey}: seeded", str_contains($seed, "'{$settingKey}'"));
    }
}

// ── Seeder ↔ schema columns ─────────────────────────────────────────────────
// This is the check that would have caught the seeder writing `sort_order`
// into the `settings` table.
Harness::group('seeder columns');

preg_match_all('/CREATE TABLE IF NOT EXISTS `(\w+)` \((.*?)\n\) ENGINE/s', $schema, $tables, PREG_SET_ORDER);
$schemaColumns = [];

foreach ($tables as $table) {
    preg_match_all('/^\s+`(\w+)`/m', $table[2], $found);
    $schemaColumns[$table[1]] = $found[1];
}

preg_match_all('/seed\(\'(\w+)\', \[(.*?)\n\]\);/s', $seed, $calls, PREG_SET_ORDER);
Harness::check('seed() calls found', count($calls) >= 8, count($calls) . ' found');

foreach ($calls as [, $table, $body]) {
    Harness::check("{$table}: table exists", isset($schemaColumns[$table]));

    if (!isset($schemaColumns[$table])) {
        continue;
    }

    preg_match_all('/\'(\w+)\'\s*=>/', $body, $keys);
    $used = array_unique($keys[1]);

    $unknown = array_diff($used, $schemaColumns[$table]);
    Harness::check("{$table}: no unknown columns", $unknown === [], implode(', ', $unknown));

    // NOT NULL columns without a default must be supplied by the seeder.
    preg_match('/CREATE TABLE IF NOT EXISTS `' . $table . '` \((.*?)\n\) ENGINE/s', $schema, $m);
    preg_match_all('/^\s+`(\w+)`[^,\n]*NOT NULL(?!.*DEFAULT)(?!.*AUTO_INCREMENT)/m', $m[1] ?? '', $required);

    $missing = array_diff($required[1], $used, ['id']);
    Harness::check("{$table}: required columns supplied", $missing === [], implode(', ', $missing));
}

// ── Validator ───────────────────────────────────────────────────────────────
Harness::group('validator');

$cases = [
    ['required rejects empty',        ['a' => ''],                      ['a' => ['required']],              false],
    ['required accepts a value',      ['a' => 'x'],                     ['a' => ['required']],              true],
    ['max rejects overlong',          ['a' => 'abcd'],                  ['a' => ['max:3']],                 false],
    ['max accepts boundary',          ['a' => 'abc'],                   ['a' => ['max:3']],                 true],
    ['min rejects short',             ['a' => 'ab'],                    ['a' => ['min:3']],                 false],
    ['email rejects bad',             ['a' => 'nope'],                  ['a' => ['email']],                 false],
    ['email accepts good',            ['a' => 'x@y.co'],                ['a' => ['email']],                 true],
    ['url rejects bad',               ['a' => 'not a url'],             ['a' => ['url']],                   false],
    ['url accepts good',              ['a' => 'https://x.co'],          ['a' => ['url']],                   true],
    ['integer rejects text',          ['a' => 'abc'],                   ['a' => ['integer']],               false],
    ['integer accepts zero',          ['a' => '0'],                     ['a' => ['integer']],               true],
    ['between rejects above',         ['a' => '101'],                   ['a' => ['between:0,100']],         false],
    ['between accepts inside',        ['a' => '50'],                    ['a' => ['between:0,100']],         true],
    ['in rejects unknown',            ['a' => 'zzz'],                   ['a' => ['in:x,y']],                false],
    ['in accepts listed',            ['a' => 'y'],                     ['a' => ['in:x,y']],                true],
    ['confirmed rejects mismatch',    ['a' => '1', 'a_confirmation' => '2'], ['a' => ['confirmed']],        false],
    ['confirmed accepts match',       ['a' => '1', 'a_confirmation' => '1'], ['a' => ['confirmed']],        true],
    ['blank skips optional rules',    ['a' => ''],                      ['a' => ['url', 'max:2']],          true],
    ['required list rejects empty',   ['a' => []],                      ['a' => ['required']],              false],
];

foreach ($cases as [$label, $data, $rules, $shouldPass]) {
    Harness::check($label, (new Validator($data, $rules))->passes() === $shouldPass);
}

// ── Router ──────────────────────────────────────────────────────────────────
Harness::group('router');

$router     = require $root . '/config/routes.php';
$reflection = new ReflectionClass($router);
$property   = $reflection->getProperty('routes');
$property->setAccessible(true);
/** @var array<string, array<int, array>> $routes */
$routes = $property->getValue($router);

$resolve = static function (string $method, string $path) use ($routes): ?array {
    foreach ($routes[$method] ?? [] as $route) {
        if (preg_match($route['pattern'], $path)) {
            return $route;
        }
    }

    return null;
};

$name = static function (?array $route): string {
    if ($route === null) {
        return '(no match)';
    }

    return substr((string) strrchr($route['handler'][0], '\\'), 1) . '::' . $route['handler'][1];
};

// Asserting the *handler*, not merely that something matched — a literal
// segment like /reorder is easily shadowed by an {id} placeholder.
$expected = [
    'GET' => [
        '/'                           => 'HomeController::index',
        '/index.html'                 => 'HomeController::legacyIndex',
        '/contact'                    => 'ContactController::show',
        '/contact.html'               => 'ContactController::legacyContact',
        '/admin'                      => 'DashboardController::index',
        '/admin/login'                => 'AuthController::showLogin',
        '/admin/content/works'        => 'ResourceController::index',
        '/admin/content/works/create' => 'ResourceController::create',
        '/admin/content/works/7/edit' => 'ResourceController::edit',
        '/admin/settings'             => 'SettingController::index',
        '/admin/settings/seo'         => 'SettingController::show',
        '/admin/media'                => 'MediaController::index',
        '/admin/messages'             => 'MessageController::index',
        '/admin/messages/3'           => 'MessageController::show',
        '/admin/users'                => 'UserController::index',
        '/admin/users/create'         => 'UserController::create',
        '/admin/users/2/edit'         => 'UserController::edit',
    ],
    'POST' => [
        '/contact'                        => 'ContactController::submit',
        '/admin/login'                    => 'AuthController::login',
        '/admin/logout'                   => 'AuthController::logout',
        '/admin/content/works'            => 'ResourceController::store',
        '/admin/content/works/7'          => 'ResourceController::update',
        '/admin/content/works/7/delete'   => 'ResourceController::destroy',
        '/admin/content/works/7/toggle'   => 'ResourceController::togglePublished',
        '/admin/content/works/reorder'    => 'ResourceController::reorder',
        '/admin/settings/seo'             => 'SettingController::update',
        '/admin/media'                    => 'MediaController::store',
        '/admin/media/1/delete'           => 'MediaController::destroy',
        '/admin/messages/read-all'        => 'MessageController::markAllRead',
        '/admin/messages/3/delete'        => 'MessageController::destroy',
        '/admin/users'                    => 'UserController::store',
        '/admin/users/2'                  => 'UserController::update',
        '/admin/users/2/delete'           => 'UserController::destroy',
    ],
];

foreach ($expected as $method => $paths) {
    foreach ($paths as $path => $handler) {
        $actual = $name($resolve($method, $path));
        Harness::check("{$method} {$path} → {$handler}", $actual === $handler, "got {$actual}");
    }
}

Harness::check('unknown path does not match', $resolve('GET', '/no/such/page') === null);

// Every controller referenced by a route must actually be callable.
foreach ($routes as $method => $list) {
    foreach ($list as $route) {
        [$class, $action] = $route['handler'];
        Harness::check(
            "{$method} handler exists: " . $name($route),
            class_exists($class) && method_exists($class, $action)
        );
    }
}

// Nothing under /admin may be reachable without middleware (login excepted).
$unprotected = [];

foreach ($routes as $list) {
    foreach ($list as $route) {
        $isAdmin = str_contains($route['pattern'], 'admin');
        $isLogin = str_contains($route['pattern'], 'admin/login');

        if ($isAdmin && !$isLogin && $route['middleware'] === []) {
            $unprotected[] = $route['pattern'];
        }
    }
}

Harness::check('all admin routes carry middleware', $unprotected === [], implode(' ', $unprotected));

// ── Views ───────────────────────────────────────────────────────────────────
Harness::group('error views');

$notFound = View::render('errors/404', ['pageTitle' => 'Not found'], 'layouts/minimal');
Harness::check('404 renders', str_contains($notFound, '<h1>404</h1>'));
Harness::check('404 sets its title', str_contains($notFound, '<title>Not found</title>'));
Harness::check('404 is noindex', str_contains($notFound, 'noindex'));
Harness::check('404 needs no database', !str_contains($notFound, 'stylesheet'));

$serverError = View::render('errors/500', ['pageTitle' => 'Error'], 'layouts/minimal');
Harness::check('500 renders', str_contains($serverError, '<h1>500</h1>'));

// ── Helpers ─────────────────────────────────────────────────────────────────
Harness::group('helpers');

Harness::check('e() escapes markup', e('<b>&"') === '&lt;b&gt;&amp;&quot;');
Harness::check('e() escapes single quotes', e("O'x") === 'O&#039;x');
Harness::check('asset() fingerprints real files', str_contains(asset('assets/css/style.css'), '?v='));
Harness::check('asset() leaves missing files bare', asset('assets/css/nope.css') === '/assets/css/nope.css');
Harness::check('upload_url() tolerates blank', upload_url('') === '');
Harness::check('upload_url() passes absolute through', upload_url('https://x/y.png') === 'https://x/y.png');
Harness::check('upload_url() roots relative paths', upload_url('assets/x.png') === '/assets/x.png');
Harness::check('comma_lines() trims', comma_lines('A, B ,C') === ['A', 'B', 'C']);
Harness::check('comma_lines() drops blanks', comma_lines('A,,B') === ['A', 'B']);

// ── Request ─────────────────────────────────────────────────────────────────
Harness::group('request');

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI']    = '/admin/content/works/?page=2';
$_POST = ['_method' => 'DELETE', 'padded' => '  x  ', 'list' => ['a', '', 'b'], 'flag' => 'on'];
$_GET  = ['page' => '2'];

$request = new Request();
Harness::check('honours _method override', $request->method() === 'DELETE');
Harness::check('rejects bogus _method', (static function (): bool {
    $_POST['_method'] = 'NONSENSE';
    $result = (new Request())->method() === 'POST';
    $_POST['_method'] = 'DELETE';

    return $result;
})());
Harness::check('trims input', $request->input('padded') === 'x');
Harness::check('falls back to default', $request->input('absent', 'fb') === 'fb');
Harness::check('inputArray drops blanks', $request->inputArray('list') === ['a', 'b']);
Harness::check('inputArray on missing key', $request->inputArray('nope') === []);
Harness::check('boolean reads checkbox', $request->boolean('flag') === true);
Harness::check('boolean false when absent', $request->boolean('nope') === false);
Harness::check('path drops query and trailing slash', $request->path() === '/admin/content/works');
Harness::check('reads query params', $request->input('page') === '2');

$_POST = [];
$_GET  = [];
