<?php

namespace App\Controllers;

use App\Models\LoginAttempt;

class RateLimiter
{
    private LoginAttempt $loginAttempt;
    private int $maxAttempts;
    private int $lockoutTime;

    public function __construct(int $maxAttempts = 5, int $lockoutTime = 900)
    {
        $this->loginAttempt = new LoginAttempt();
        $this->maxAttempts = $maxAttempts;
        $this->lockoutTime = $lockoutTime;
    }

    public function recordAttempt(string $username, string $ip, bool $success): void
    {
        $this->loginAttempt->record($username, $ip, $success);
    }

    public function isLockedOut(string $ip): bool
    {
        $attempts = $this->loginAttempt->countFailedByIp($ip, $this->lockoutTime);
        return $attempts >= $this->maxAttempts;
    }
}
