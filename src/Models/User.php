<?php

namespace PhpAuth\Models;

use PGDatabase\Models\Model;
use PGDatabase\Postgres;

class User extends Model
{
    protected string $TABLE_NAME = 'public.users';

    public function setRequest(array $request): void
    {
        $this->setValues($request);
    }

    public function getData(): array
    {
        return $this->getValues();
    }

    public function findByUsername(string $username): ?array
    {
        $rows = Postgres::fetchAllParams(
            "SELECT u.*, r.name as role_name
             FROM public.users u
             LEFT JOIN public.roles r ON u.role_id = r.id
             WHERE u.username = $1",
            [$username]
        );
        return $rows[0] ?? null;
    }

    public function findByEmail(string $email): ?array
    {
        $rows = Postgres::fetchAllParams(
            "SELECT u.*, r.name as role_name
             FROM public.users u
             LEFT JOIN public.roles r ON u.role_id = r.id
             WHERE u.email = $1",
            [$email]
        );
        return $rows[0] ?? null;
    }

    public function hasPermission(int $userId, string $permission): bool
    {
        $rows = Postgres::fetchAllParams(
            "SELECT 1
             FROM public.users u
             JOIN public.role_permissions rp ON u.role_id = rp.role_id
             JOIN public.permissions p ON rp.permission_id = p.id
             WHERE u.id = $1 AND p.name = $2",
            [(string)$userId, $permission]
        );
        return !empty($rows);
    }

    public function createUser(string $username, string $email, string $passwordHash, int $roleId): int
    {
        return Postgres::insert($this->TABLE_NAME, [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role_id' => $roleId
        ]);
    }
}
