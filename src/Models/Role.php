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
}
