<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected ?string $orderBy = 'name';

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return Database::selectOne(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => strtolower(trim($email))]
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): int
    {
        $attributes['email']    = strtolower(trim((string) $attributes['email']));
        $attributes['password'] = password_hash((string) $attributes['password'], PASSWORD_DEFAULT);

        return parent::create($attributes);
    }

    public function updatePassword(int $id, string $plainPassword): void
    {
        Database::execute(
            'UPDATE users SET password = :password WHERE id = :id',
            ['password' => password_hash($plainPassword, PASSWORD_DEFAULT), 'id' => $id]
        );
    }

    public function touchLastLogin(int $id): void
    {
        Database::execute('UPDATE users SET last_login_at = NOW() WHERE id = :id', ['id' => $id]);
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql      = 'SELECT id FROM users WHERE email = :email';
        $bindings = ['email' => strtolower(trim($email))];

        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $bindings['id'] = $exceptId;
        }

        return Database::selectOne($sql, $bindings) !== null;
    }
}
