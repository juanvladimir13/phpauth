<?php

namespace PhpAuth\Models;

use PGDatabase\Models\Model;
use PGDatabase\Postgres;

class LoginAttempt extends Model
{
    protected string $TABLE_NAME = 'phpauth.login_attempts';

    public function setRequest(array $request): void
    {
        $this->setValues($request);
    }

    public function getData(): array
    {
        return $this->getValues();
    }

    public function record(string $username, string $ip, bool $success): void
    {
        Postgres::insert($this->TABLE_NAME, [
            'username' => $username,
            'ip_address' => $ip,
            'successful' => $success ? 'true' : 'false'
        ]);
    }

    public function countFailedByIp(string $ip, int $lockoutSeconds): int
    {
        $rows = Postgres::fetchAllParams(
            "SELECT COUNT(*) as cnt FROM {$this->TABLE_NAME}
             WHERE ip_address = $1
             AND successful = FALSE
             AND attempt_time > (CURRENT_TIMESTAMP - ($2 || ' seconds')::interval)",
            [$ip, (string)$lockoutSeconds]
        );
        return (int)($rows[0]['cnt'] ?? 0);
    }

    public function countFailedByUsername(string $username, int $lockoutSeconds): int
    {
        $rows = Postgres::fetchAllParams(
            "SELECT COUNT(*) as total
             FROM {$this->TABLE_NAME}
             WHERE username = $1
             AND successful = false
             AND attempt_time > CURRENT_TIMESTAMP - ($2 || ' seconds')::interval",
            [$username, (string)$lockoutSeconds]
        );
        return (int)($rows[0]['total'] ?? 0);
    }
}
