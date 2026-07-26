<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Models\Media;

/**
 * Every image an editor can pick, from two sources:
 *
 *  - files uploaded through the admin (tracked in the `media` table)
 *  - images that shipped with the site under public/assets/images
 *
 * The bundled images are discovered on disk rather than imported into the
 * database, so nothing has to be kept in sync.
 */
final class MediaLibrary
{
    private const BUNDLED_ROOT = 'assets/images';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    /**
     * Paths grouped by folder label, ready to render as an <optgroup> list.
     *
     * @return array<string, array<int, string>>
     */
    public function grouped(): array
    {
        $groups = [];

        foreach ((new Media())->all() as $item) {
            $groups['Uploads'][] = $item['path'];
        }

        foreach ($this->bundled() as $path) {
            $folder = dirname($path);
            $label  = $folder === self::BUNDLED_ROOT
                ? 'Images'
                : ucfirst(str_replace('-', ' ', basename($folder)));

            $groups[$label][] = $path;
        }

        return $groups;
    }

    /**
     * Uploaded files, newest first, for the media management screen.
     *
     * @return array<int, array<string, mixed>>
     */
    public function uploads(): array
    {
        return (new Media())->all();
    }

    /**
     * Image paths bundled with the original site, relative to /public.
     *
     * @return array<int, string>
     */
    public function bundled(): array
    {
        $root = Config::get('app.public_path') . '/' . self::BUNDLED_ROOT;

        if (!is_dir($root)) {
            return [];
        }

        $paths    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if (!in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $paths[] = self::BUNDLED_ROOT . '/' . ltrim(
                str_replace('\\', '/', substr($file->getPathname(), strlen($root))),
                '/'
            );
        }

        sort($paths);

        return $paths;
    }

    /**
     * Non-image assets an editor may need to link, e.g. the résumé PDF.
     *
     * @return array<int, string>
     */
    public function documents(): array
    {
        $root = Config::get('app.public_path') . '/assets';

        if (!is_dir($root)) {
            return [];
        }

        $paths = [];

        foreach ((array) glob($root . '/*.{pdf,doc,docx}', GLOB_BRACE) as $file) {
            $paths[] = 'assets/' . basename((string) $file);
        }

        foreach ((array) glob(Config::get('app.upload_path') . '/*.{pdf,doc,docx}', GLOB_BRACE) as $file) {
            $paths[] = 'assets/uploads/' . basename((string) $file);
        }

        sort($paths);

        return $paths;
    }
}
