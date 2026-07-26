<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Plain-PHP template renderer.
 *
 * A template is rendered to a string first, then injected into a layout as
 * $content. Templates access helpers through the global functions declared in
 * app/helpers.php (notably e() for escaping).
 */
final class View
{
    private static string $path = '';

    /** @var array<string, mixed> Data merged into every render. */
    private static array $shared = [];

    public static function setPath(string $path): void
    {
        self::$path = rtrim($path, '/');
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        $content = self::capture($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::capture($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * Render a partial in-place — used from inside templates.
     *
     * @param array<string, mixed> $data
     */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function capture(string $template, array $data): string
    {
        $file = self::$path . '/' . str_replace('.', '/', $template) . '.php';

        if (!is_readable($file)) {
            throw new RuntimeException("View [{$template}] not found at {$file}.");
        }

        extract(array_merge(self::$shared, $data), EXTR_SKIP);

        ob_start();

        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }
}
