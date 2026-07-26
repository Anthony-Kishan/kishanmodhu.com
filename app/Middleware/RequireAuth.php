<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Blocks unauthenticated access to the admin area.
 *
 * The originally requested path is stashed so login can bounce the user back
 * to where they were heading.
 */
final class RequireAuth
{
    public function handle(Request $request): ?Response
    {
        if (Auth::check()) {
            return null;
        }

        Session::put('intended_url', $request->path());
        Session::flash('error', 'Please sign in to continue.');

        return Response::redirect('/admin/login');
    }
}
