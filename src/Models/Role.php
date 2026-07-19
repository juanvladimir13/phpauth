<?php

namespace App\Models;

use PGDatabase\Models\Model;
use PGDatabase\Postgres;

class Role extends Model
{
    protected string $TABLE_NAME = 'public.roles';

    public function setRequest(array $request): void
    {
        $this->setValues($request);
    }

    public function getData(): array
    {
        return $this->getValues();
    }

    public function findByName(string $name): ?array
    {
        $rows = Postgres::fetchAllParams(
            "SELECT * FROM public.roles WHERE name = $1",
            [$name]
        );
        return $rows[0] ?? null;
    }

    public function create(string $name): int
    {
        return Postgres::insert($this->TABLE_NAME, ['name' => $name]);
    }

    public function assignPermission(int $roleId, int $permissionId): void
    {
        Postgres::insert('public.role_permissions', [
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
    }

    public function getPermissions(int $roleId): array
    {
        return Postgres::fetchAllParams(
            "SELECT p.*
             FROM public.permissions p
             JOIN public.role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = $1
             ORDER BY p.name",
            [(string)$roleId]
        );
    }
}
