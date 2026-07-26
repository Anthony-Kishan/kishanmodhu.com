<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Authorisation layer for actions restricted to the `admin` role.
 *
 * Editors can manage content but not other user accounts.
 */
final class RequireAdmin
{
    public function handle(Request $request): ?Response
    {
        if (Auth::isAdmin()) {
            return null;
        }

        Session::flash('error', 'You do not have permission to do that.');

        return Response::redirect('/admin');
    }
}
