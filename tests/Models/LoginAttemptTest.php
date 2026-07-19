<?php

namespace Tests\Models;

use App\Models\LoginAttempt;
use PHPUnit\Framework\TestCase;

class LoginAttemptTest extends TestCase
{
    private LoginAttempt $loginAttempt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAttempt = new LoginAttempt();
    }

    public function testTableNameIsPublicLoginAttempts(): void
    {
        $reflection = new \ReflectionProperty(LoginAttempt::class, 'TABLE_NAME');
        $reflection->setAccessible(true);

        $this->assertSame('public.login_attempts', $reflection->getValue($this->loginAttempt));
    }

    public function testSetRequestStoresValues(): void
    {
        $data = ['username' => 'test', 'ip_address' => '127.0.0.1', 'successful' => 't'];
        $this->loginAttempt->setRequest($data);

        $this->assertSame($data, $this->loginAttempt->getData());
    }

    public function testGetDataReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->loginAttempt->getData());
    }

    public function testCountFailedByIpReturnsZeroWhenNoDatabase(): void
    {
        $result = $this->loginAttempt->countFailedByIp('127.0.0.1', 900);

        $this->assertSame(0, $result);
    }
}
