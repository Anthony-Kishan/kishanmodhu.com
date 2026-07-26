<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

/**
 * Administrator account management. Restricted to the `admin` role.
 */
final class UserController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('admin/users/index', [
            'pageTitle' => 'Users',
            'records'   => (new User())->all(),
        ], 'layouts/admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/users/form', [
            'pageTitle' => 'New user',
            'record'    => null,
            'action'    => '/admin/users',
        ], 'layouts/admin');
    }

    public function store(Request $request): Response
    {
        $input = $this->collect($request);

        $validator = new Validator(
            $input + ['password_confirmation' => (string) $request->input('password_confirmation')],
            [
                'name'     => ['required', 'max:120'],
                'email'    => ['required', 'email', 'max:160'],
                'password' => ['required', 'min:12', 'confirmed'],
                'role'     => ['required', 'in:admin,editor'],
            ],
            ['name' => 'Name', 'email' => 'Email', 'password' => 'Password', 'role' => 'Role']
        );

        if ($validator->fails()) {
            return $this->redirectWithErrors('/admin/users/create', $validator->errors(), $input);
        }

        $users = new User();

        if ($users->emailExists($input['email'])) {
            return $this->redirectWithErrors('/admin/users/create', ['email' => 'That email is already registered.'], $input);
        }

        $users->create($input + ['is_active' => $request->boolean('is_active') ? 1 : 0]);

        return $this->redirectWithSuccess('/admin/users', 'User created.');
    }

    public function edit(Request $request, string $id): Response
    {
        $record = (new User())->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        unset($record['password']);

        return $this->view('admin/users/form', [
            'pageTitle' => 'Edit user',
            'record'    => $record,
            'action'    => '/admin/users/' . (int) $id,
        ], 'layouts/admin');
    }

    public function update(Request $request, string $id): Response
    {
        $users  = new User();
        $record = $users->find((int) $id);

        if ($record === null) {
            return Response::notFound();
        }

        $userId   = (int) $id;
        $input    = $this->collect($request);
        $password = (string) $request->input('password');

        $rules = [
            'name'  => ['required', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'role'  => ['required', 'in:admin,editor'],
        ];

        // Password is optional on edit; only validated when a new one is given.
        if ($password !== '') {
            $rules['password'] = ['min:12', 'confirmed'];
        }

        $validator = new Validator(
            $input + [
                'password'              => $password,
                'password_confirmation' => (string) $request->input('password_confirmation'),
            ],
            $rules,
            ['name' => 'Name', 'email' => 'Email', 'password' => 'Password', 'role' => 'Role']
        );

        if ($validator->fails()) {
            return $this->redirectWithErrors('/admin/users/' . $userId . '/edit', $validator->errors(), $input);
        }

        if ($users->emailExists($input['email'], $userId)) {
            return $this->redirectWithErrors('/admin/users/' . $userId . '/edit', ['email' => 'That email is already registered.'], $input);
        }

        $isSelf    = $userId === (int) (Auth::user()['id'] ?? 0);
        $isActive  = $request->boolean('is_active') ? 1 : 0;
        $role      = $input['role'];

        // Do not let an admin lock themselves out of the panel.
        if ($isSelf) {
            $isActive = 1;
            $role     = 'admin';
        }

        $users->update($userId, [
            'name'      => $input['name'],
            'email'     => strtolower($input['email']),
            'role'      => $role,
            'is_active' => $isActive,
        ]);

        if ($password !== '') {
            $users->updatePassword($userId, $password);
        }

        return $this->redirectWithSuccess('/admin/users', 'User updated.');
    }

    public function destroy(Request $request, string $id): Response
    {
        $userId = (int) $id;

        if ($userId === (int) (Auth::user()['id'] ?? 0)) {
            Session::flash('error', 'You cannot delete your own account.');

            return $this->redirect('/admin/users');
        }

        (new User())->delete($userId);

        return $this->redirectWithSuccess('/admin/users', 'User deleted.');
    }

    /**
     * @return array<string, string>
     */
    private function collect(Request $request): array
    {
        return [
            'name'     => (string) $request->input('name'),
            'email'    => (string) $request->input('email'),
            'password' => (string) $request->input('password'),
            'role'     => (string) $request->input('role'),
        ];
    }
}
