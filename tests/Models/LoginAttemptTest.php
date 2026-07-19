<?php

namespace Tests\Models;

use PhpAuth\Models\LoginAttempt;
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

    public function testGetDataReturnsStoredValues(): void
    {
        $data = ['username' => 'testuser', 'ip_address' => '127.0.0.1'];
        $this->loginAttempt->setRequest($data);

        $result = $this->loginAttempt->getData();

        $this->assertSame('testuser', $result['username']);
        $this->assertSame('127.0.0.1', $result['ip_address']);
    }

    public function testRecordReturnsVoidWhenNoDatabase(): void
    {
        $this->expectNotToPerformAssertions();
        $this->loginAttempt->record('user', 'ip', true);
    }

    public function testCountFailedByUsernameReturnsZeroWhenNoDatabase(): void
    {
        $result = $this->loginAttempt->countFailedByUsername('user', 900);
        $this->assertSame(0, $result);
    }
}
