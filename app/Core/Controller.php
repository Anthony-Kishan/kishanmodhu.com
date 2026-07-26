<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $template, array $data = [], string $layout = 'layouts/site', int $status = 200): Response
    {
        return Response::view($template, $data, $layout, $status);
    }

    protected function redirect(string $url): Response
    {
        return Response::redirect($url);
    }

    /**
     * Redirect back to a form, preserving the submitted values and errors.
     *
     * @param array<string, string> $errors
     * @param array<string, mixed>  $input
     */
    protected function redirectWithErrors(string $url, array $errors, array $input): Response
    {
        Session::flash('errors', $errors);
        Session::flashInput($input);

        return Response::redirect($url);
    }

    protected function redirectWithSuccess(string $url, string $message): Response
    {
        Session::flash('success', $message);

        return Response::redirect($url);
    }

    /**
     * Reject any state-changing request whose CSRF token is missing or stale.
     */
    protected function requireValidToken(Request $request): ?Response
    {
        if (Csrf::verify((string) $request->input('_token'))) {
            return null;
        }

        Session::flash('error', 'Your session expired. Please try again.');

        return Response::redirect($request->path());
    }
}
