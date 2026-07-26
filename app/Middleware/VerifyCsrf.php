<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Rejects state-changing requests carrying a missing or stale CSRF token.
 */
final class VerifyCsrf
{
    public function handle(Request $request): ?Response
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        if (Csrf::verify((string) $request->input('_token'))) {
            return null;
        }

        Session::flash('error', 'Your session expired. Please try that again.');

        return Response::redirect($request->path());
    }
}
