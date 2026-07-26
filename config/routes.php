<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\MessageController;
use App\Controllers\Admin\ResourceController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\UserController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\HomeController;
use App\Core\Router;
use App\Middleware\RequireAdmin;
use App\Middleware\RequireAuth;
use App\Middleware\VerifyCsrf;

$router = new Router();

// ── Public site ─────────────────────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);
$router->get('/contact', [ContactController::class, 'show']);
$router->post('/contact', [ContactController::class, 'submit']);

// Preserve inbound links to the old static filenames.
$router->get('/index.html', [HomeController::class, 'legacyIndex']);
$router->get('/contact.html', [ContactController::class, 'legacyContact']);

// ── Admin authentication ────────────────────────────────────────────────────
$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);

// ── Admin area (authenticated) ──────────────────────────────────────────────
$router->group('/admin', [RequireAuth::class, VerifyCsrf::class], static function (Router $router): void {
    $router->post('/logout', [AuthController::class, 'logout']);

    $router->get('', [DashboardController::class, 'index']);

    // Generic CRUD driven by config/content_types.php.
    $router->get('/content/{type}', [ResourceController::class, 'index']);
    $router->get('/content/{type}/create', [ResourceController::class, 'create']);
    $router->post('/content/{type}', [ResourceController::class, 'store']);
    $router->get('/content/{type}/{id:[0-9]+}/edit', [ResourceController::class, 'edit']);
    $router->post('/content/{type}/{id:[0-9]+}', [ResourceController::class, 'update']);
    $router->post('/content/{type}/{id:[0-9]+}/delete', [ResourceController::class, 'destroy']);
    $router->post('/content/{type}/{id:[0-9]+}/toggle', [ResourceController::class, 'togglePublished']);
    $router->post('/content/{type}/reorder', [ResourceController::class, 'reorder']);

    // Settings.
    $router->get('/settings', [SettingController::class, 'index']);
    $router->get('/settings/{group}', [SettingController::class, 'show']);
    $router->post('/settings/{group}', [SettingController::class, 'update']);

    // Contact inbox.
    $router->get('/messages', [MessageController::class, 'index']);
    $router->get('/messages/{id:[0-9]+}', [MessageController::class, 'show']);
    $router->post('/messages/{id:[0-9]+}/delete', [MessageController::class, 'destroy']);
    $router->post('/messages/read-all', [MessageController::class, 'markAllRead']);

    // Media library.
    $router->get('/media', [MediaController::class, 'index']);
    $router->post('/media', [MediaController::class, 'store']);
    $router->post('/media/{id:[0-9]+}/delete', [MediaController::class, 'destroy']);
});

// ── User management (admin role only) ───────────────────────────────────────
$router->group('/admin/users', [RequireAuth::class, RequireAdmin::class, VerifyCsrf::class], static function (Router $router): void {
    $router->get('', [UserController::class, 'index']);
    $router->get('/create', [UserController::class, 'create']);
    $router->post('', [UserController::class, 'store']);
    $router->get('/{id:[0-9]+}/edit', [UserController::class, 'edit']);
    $router->post('/{id:[0-9]+}', [UserController::class, 'update']);
    $router->post('/{id:[0-9]+}/delete', [UserController::class, 'destroy']);
});

return $router;
