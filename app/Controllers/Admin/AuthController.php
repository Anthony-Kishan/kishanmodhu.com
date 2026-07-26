<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;

final class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirect('/admin');
        }

        return $this->view('admin/auth/login', [
            'pageTitle' => 'Sign in',
        ], 'layouts/auth');
    }

    public function login(Request $request): Response
    {
        if (!Csrf::verify((string) $request->input('_token'))) {
            Session::flash('error', 'Your session expired. Please try again.');

            return $this->redirect('/admin/login');
        }

        if (Auth::isLockedOut()) {
            Session::flash('error', sprintf(
                'Too many failed attempts. Try again in %d minutes.',
                (int) ceil(Auth::secondsUntilUnlock() / 60)
            ));

            return $this->redirect('/admin/login');
        }

        $input = [
            'email'    => (string) $request->input('email'),
            'password' => (string) $request->input('password'),
        ];

        $validator = new Validator($input, [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->redirectWithErrors('/admin/login', $validator->errors(), ['email' => $input['email']]);
        }

        if (!Auth::attempt($input['email'], $input['password'])) {
            // Deliberately vague: do not reveal whether the address exists.
            Session::flash('error', 'Those credentials do not match our records.');
            Session::flashInput(['email' => $input['email']]);

            return $this->redirect('/admin/login');
        }

        $intended = (string) Session::get('intended_url', '/admin');
        Session::forget('intended_url');

        return $this->redirect(str_starts_with($intended, '/admin') ? $intended : '/admin');
    }

    public function logout(Request $request): Response
    {
        Auth::logout();

        return $this->redirect('/admin/login');
    }
}
