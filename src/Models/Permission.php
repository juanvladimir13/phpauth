<?php

namespace PhpAuth\Models;

use PGDatabase\Models\Model;
use PGDatabase\Postgres;

class Permission extends Model
{
    protected string $TABLE_NAME = 'public.permissions';

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
            "SELECT * FROM public.permissions WHERE name = $1",
            [$name]
        );
        return $rows[0] ?? null;
    }

    public function create(string $name): int
    {
        return Postgres::insert($this->TABLE_NAME, ['name' => $name]);
    }
}
