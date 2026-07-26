<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\ContentType;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\MediaLibrary;

/**
 * One controller serving CRUD for every registered content type.
 *
 * Behaviour comes entirely from config/content_types.php, so adding a content
 * type needs no new controller, routes or views.
 */
final class ResourceController extends Controller
{
    public function index(Request $request, string $type): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null) {
            return Response::notFound();
        }

        return $this->view('admin/resources/index', [
            'pageTitle'   => $contentType->label(),
            'contentType' => $contentType,
            'records'     => $contentType->model()->all(),
        ], 'layouts/admin');
    }

    public function create(Request $request, string $type): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null) {
            return Response::notFound();
        }

        return $this->view('admin/resources/form', [
            'pageTitle'   => 'New ' . strtolower($contentType->singular()),
            'contentType' => $contentType,
            'record'      => null,
            'mediaGroups' => (new MediaLibrary())->grouped(),
            'action'      => $contentType->adminUrl(),
        ], 'layouts/admin');
    }

    public function store(Request $request, string $type): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null) {
            return Response::notFound();
        }

        $input     = $this->collect($request, $contentType);
        $validator = new Validator($input, $contentType->rules(), $contentType->labels());

        if ($validator->fails()) {
            return $this->redirectWithErrors($contentType->adminUrl('/create'), $validator->errors(), $input);
        }

        $model      = $contentType->model();
        $attributes = $this->prepare($input, $contentType);

        if ($contentType->isSortable()) {
            $attributes['sort_order'] = $model->nextSortOrder();
        }

        if ($contentType->isPublishable()) {
            $attributes['is_published'] = $request->boolean('is_published') ? 1 : 0;
        }

        $model->create($attributes);

        return $this->redirectWithSuccess(
            $contentType->adminUrl(),
            $contentType->singular() . ' created.'
        );
    }

    public function edit(Request $request, string $type, string $id): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null) {
            return Response::notFound();
        }

        $record = $contentType->model()->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        return $this->view('admin/resources/form', [
            'pageTitle'   => 'Edit ' . strtolower($contentType->singular()),
            'contentType' => $contentType,
            'record'      => $record,
            'mediaGroups' => (new MediaLibrary())->grouped(),
            'action'      => $contentType->adminUrl('/' . (int) $id),
        ], 'layouts/admin');
    }

    public function update(Request $request, string $type, string $id): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null) {
            return Response::notFound();
        }

        $model  = $contentType->model();
        $record = $model->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        $input     = $this->collect($request, $contentType);
        $validator = new Validator($input, $contentType->rules(), $contentType->labels());

        if ($validator->fails()) {
            return $this->redirectWithErrors($contentType->adminUrl('/' . (int) $id . '/edit'), $validator->errors(), $input);
        }

        $attributes = $this->prepare($input, $contentType);

        if ($contentType->isPublishable()) {
            $attributes['is_published'] = $request->boolean('is_published') ? 1 : 0;
        }

        $model->update((int) $id, $attributes);

        return $this->redirectWithSuccess(
            $contentType->adminUrl(),
            $contentType->singular() . ' updated.'
        );
    }

    public function destroy(Request $request, string $type, string $id): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null) {
            return Response::notFound();
        }

        $contentType->model()->delete((int) $id);

        return $this->redirectWithSuccess(
            $contentType->adminUrl(),
            $contentType->singular() . ' deleted.'
        );
    }

    public function togglePublished(Request $request, string $type, string $id): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null || !$contentType->isPublishable()) {
            return Response::notFound();
        }

        $model  = $contentType->model();
        $record = $model->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        $published = (int) $record['is_published'] === 1 ? 0 : 1;
        $model->update((int) $id, ['is_published' => $published]);

        return $this->redirectWithSuccess(
            $contentType->adminUrl(),
            $published === 1 ? 'Item is now visible on the site.' : 'Item is now hidden from the site.'
        );
    }

    public function reorder(Request $request, string $type): Response
    {
        $contentType = $this->resolve($type);

        if ($contentType === null || !$contentType->isSortable()) {
            return Response::notFound();
        }

        $ids = array_map('intval', $request->inputArray('order'));

        if ($ids !== []) {
            $contentType->model()->reorder($ids);
        }

        return Response::json(['ok' => true]);
    }

    private function resolve(string $type): ?ContentType
    {
        return ContentType::exists($type) ? ContentType::find($type) : null;
    }

    /**
     * Pull the registered fields out of the request, honouring each field type.
     *
     * @return array<string, mixed>
     */
    private function collect(Request $request, ContentType $contentType): array
    {
        $input = [];

        foreach ($contentType->fields() as $name => $field) {
            $input[$name] = match ($field['type']) {
                'list'    => $request->inputArray($name),
                'boolean' => $request->boolean($name) ? '1' : '0',
                default   => (string) $request->input($name, ''),
            };
        }

        return $input;
    }

    /**
     * Convert validated input into column values.
     *
     * @param  array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function prepare(array $input, ContentType $contentType): array
    {
        $attributes = [];

        foreach ($contentType->fields() as $name => $field) {
            $attributes[$name] = match ($field['type']) {
                'list'    => json_encode(array_values((array) $input[$name]), JSON_THROW_ON_ERROR),
                'boolean' => (int) $input[$name],
                'number'  => $input[$name] === '' ? 0 : (int) $input[$name],
                default   => $input[$name] === '' ? null : $input[$name],
            };
        }

        return $attributes;
    }
}
