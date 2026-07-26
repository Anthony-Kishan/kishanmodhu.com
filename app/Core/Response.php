<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Value object describing what should be sent back to the browser.
 *
 * Controllers return one of these instead of echoing directly, which keeps
 * header handling in a single place and makes actions testable.
 */
final class Response
{
    /**
     * @param array<string, string> $headers
     */
    private function __construct(
        private readonly string $content,
        private readonly int $status = 200,
        private readonly array $headers = []
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function view(string $template, array $data = [], string $layout = 'layouts/site', int $status = 200): self
    {
        return new self(View::render($template, $data, $layout), $status);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function notFound(): self
    {
        return self::view('errors/404', ['pageTitle' => 'Page not found'], 'layouts/minimal', 404);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
