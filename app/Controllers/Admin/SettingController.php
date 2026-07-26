<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Setting;
use App\Services\MediaLibrary;

/**
 * Editing for the singleton settings groups declared in config/settings.php.
 */
final class SettingController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = Config::get('settings');

        return $this->redirect('/admin/settings/' . array_key_first($groups));
    }

    public function show(Request $request, string $group): Response
    {
        $definition = $this->group($group);

        if ($definition === null) {
            return Response::notFound();
        }

        $library = new MediaLibrary();

        return $this->view('admin/settings/show', [
            'pageTitle'   => 'Settings — ' . $definition['label'],
            'groupKey'    => $group,
            'groups'      => Config::get('settings'),
            'definition'  => $definition,
            'values'      => (new Setting())->all(),
            'mediaGroups' => $library->grouped(),
            'documents'   => $library->documents(),
        ], 'layouts/admin');
    }

    public function update(Request $request, string $group): Response
    {
        $definition = $this->group($group);

        if ($definition === null) {
            return Response::notFound();
        }

        $input  = [];
        $rules  = [];
        $labels = [];

        foreach ($definition['fields'] as $key => $field) {
            $input[$key]  = $field['type'] === 'boolean'
                ? ($request->boolean($key) ? '1' : '0')
                : (string) $request->input($key, '');
            $rules[$key]  = $field['rules'] ?? [];
            $labels[$key] = $field['label'];
        }

        $validator = new Validator($input, $rules, $labels);

        if ($validator->fails()) {
            return $this->redirectWithErrors('/admin/settings/' . $group, $validator->errors(), $input);
        }

        (new Setting())->setMany($input);

        return $this->redirectWithSuccess('/admin/settings/' . $group, $definition['label'] . ' settings saved.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function group(string $key): ?array
    {
        return Config::get('settings')[$key] ?? null;
    }
}
