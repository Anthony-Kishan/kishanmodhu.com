<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Small regex-backed router.
 *
 * Routes are registered as literal paths with optional {placeholder} segments,
 * which are handed to the controller action as ordered arguments.
 *
 * A placeholder may carry a regex constraint after a colon — `{id:[0-9]+}` —
 * which is what stops a literal segment like /content/works/reorder from being
 * captured by the /content/{type}/{id} pattern registered before it.
 */
final class Router
{
    /** @var array<string, array<int, array{pattern: string, params: array<int, string>, handler: array{0: class-string, 1: string}, middleware: array<int, string>}>> */
    private array $routes = [];

    /** @var array<int, string> */
    private array $groupMiddleware = [];

    private string $groupPrefix = '';

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * Register a batch of routes sharing a URL prefix and middleware stack.
     *
     * @param array<int, string> $middleware
     */
    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix     = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix     = $previousPrefix . $prefix;
        $this->groupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix     = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    private function add(string $method, string $path, array $handler): void
    {
        $path = $this->groupPrefix . $path;
        $path = '/' . trim($path, '/');

        $params = [];

        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}/',
            static function (array $match) use (&$params): string {
                $params[] = $match[1];

                return '(' . ($match[2] ?? '[^/]+') . ')';
            },
            $path
        ) ?? $path;

        $this->routes[$method][] = [
            'pattern'    => '#^' . $pattern . '$#',
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => $this->groupMiddleware,
        ];
    }

    /**
     * Resolve and run the matching route, or return a 404 response.
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            array_shift($matches);

            foreach ($route['middleware'] as $middleware) {
                $result = (new $middleware())->handle($request);

                if ($result instanceof Response) {
                    return $result;
                }
            }

            [$class, $action] = $route['handler'];

            return (new $class())->{$action}($request, ...$matches);
        }

        return Response::notFound();
    }
}
