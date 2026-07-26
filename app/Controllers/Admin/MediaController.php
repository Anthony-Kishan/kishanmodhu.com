<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\Media;
use App\Services\MediaLibrary;
use RuntimeException;

final class MediaController extends Controller
{
    public function index(Request $request): Response
    {
        $library = new MediaLibrary();

        return $this->view('admin/media/index', [
            'pageTitle' => 'Media',
            'uploads'   => $library->uploads(),
            'bundled'   => $library->bundled(),
        ], 'layouts/admin');
    }

    public function store(Request $request): Response
    {
        $file = $request->file('file');

        if ($file === null) {
            Session::flash('error', 'Choose a file to upload.');

            return $this->redirect('/admin/media');
        }

        try {
            $stored = (new Uploader())->store($file);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());

            return $this->redirect('/admin/media');
        }

        (new Media())->create($stored + ['uploaded_by' => Auth::user()['id'] ?? null]);

        return $this->redirectWithSuccess('/admin/media', 'File uploaded.');
    }

    public function destroy(Request $request, string $id): Response
    {
        $media  = new Media();
        $record = $media->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        (new Uploader())->delete($record['path']);
        $media->delete((int) $id);

        return $this->redirectWithSuccess('/admin/media', 'File deleted.');
    }
}
