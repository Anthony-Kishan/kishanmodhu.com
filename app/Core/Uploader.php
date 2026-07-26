<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Image upload handling for the media library.
 *
 * The extension is derived from the sniffed MIME type rather than the client
 * filename, so an uploaded "logo.php" cannot be written back as executable.
 */
final class Uploader
{
    private const MIME_EXTENSIONS = [
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/gif'     => 'gif',
        'image/webp'    => 'webp',
        'image/svg+xml' => 'svg',
    ];

    /**
     * @param  array<string, mixed> $file A single $_FILES entry.
     * @return array{path: string, filename: string, mime: string, size: int, width: int|null, height: int|null}
     */
    public function store(array $file): array
    {
        $this->assertUploadSucceeded($file);

        $maxBytes = (int) Config::get('app.max_upload_bytes', 5 * 1024 * 1024);

        if ($file['size'] > $maxBytes) {
            throw new RuntimeException(sprintf('File is larger than the %dMB limit.', intdiv($maxBytes, 1048576)));
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';

        if (!isset(self::MIME_EXTENSIONS[$mime])) {
            throw new RuntimeException('Only JPG, PNG, GIF, WebP and SVG images may be uploaded.');
        }

        // SVG can carry scripts; only accept it from a trusted admin session and
        // strip the most obvious executable content.
        if ($mime === 'image/svg+xml') {
            $this->assertSvgIsSafe($file['tmp_name']);
        }

        $extension = self::MIME_EXTENSIONS[$mime];
        $basename  = $this->slugify(pathinfo((string) $file['name'], PATHINFO_FILENAME));
        $filename  = sprintf('%s-%s.%s', $basename, bin2hex(random_bytes(4)), $extension);

        $directory = Config::get('app.upload_path');

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Upload directory is not writable.');
        }

        $destination = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded file.');
        }

        chmod($destination, 0644);

        $dimensions = $extension === 'svg' ? [null, null] : (@getimagesize($destination) ?: [null, null]);

        return [
            'path'     => 'assets/uploads/' . $filename,
            'filename' => $filename,
            'mime'     => $mime,
            'size'     => (int) $file['size'],
            'width'    => $dimensions[0] !== null ? (int) $dimensions[0] : null,
            'height'   => $dimensions[1] !== null ? (int) $dimensions[1] : null,
        ];
    }

    public function delete(string $relativePath): void
    {
        // Only ever remove files that live directly inside the uploads folder.
        $filename = basename($relativePath);
        $full     = Config::get('app.upload_path') . '/' . $filename;

        if (is_file($full)) {
            unlink($full);
        }
    }

    /**
     * @param array<string, mixed> $file
     */
    private function assertUploadSucceeded(array $file): void
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
            return;
        }

        throw new RuntimeException(match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
            UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE                        => 'No file was selected.',
            default                                   => 'The file could not be uploaded.',
        });
    }

    private function assertSvgIsSafe(string $path): void
    {
        $contents = (string) file_get_contents($path);

        if (preg_match('/<script|javascript:|on[a-z]+\s*=|<foreignObject/i', $contents)) {
            throw new RuntimeException('That SVG contains scripting and was rejected.');
        }
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value === '' ? 'file' : substr($value, 0, 60);
    }
}
