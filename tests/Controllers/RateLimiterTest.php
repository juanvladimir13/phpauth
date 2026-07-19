<?php

namespace Tests\Controllers;

use PhpAuth\Controllers\RateLimiter;
use PhpAuth\Models\LoginAttempt;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private RateLimiter $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rateLimiter = new RateLimiter(3, 900);
    }

    public function testIsLockedOutReturnsFalseWhenBelowMaxAttempts(): void
    {
        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('countFailedByIp')
            ->with('192.168.1.1', 900)
            ->willReturn(2);

        $this->setLoginAttemptMock($loginAttemptMock);

        $this->assertFalse($this->rateLimiter->isLockedOut('192.168.1.1'));
    }

    public function testIsLockedOutReturnsTrueWhenAtMaxAttempts(): void
    {
        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('countFailedByIp')
            ->with('192.168.1.1', 900)
            ->willReturn(3);

        $this->setLoginAttemptMock($loginAttemptMock);

        $this->assertTrue($this->rateLimiter->isLockedOut('192.168.1.1'));
    }

    public function testIsLockedOutReturnsTrueWhenAboveMaxAttempts(): void
    {
        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('countFailedByIp')
            ->with('10.0.0.1', 900)
            ->willReturn(5);

        $this->setLoginAttemptMock($loginAttemptMock);

        $this->assertTrue($this->rateLimiter->isLockedOut('10.0.0.1'));
    }

    public function testRecordAttemptDelegatesToLoginAttempt(): void
    {
        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('record')
            ->with('testuser', '192.168.1.1', true);

        $this->setLoginAttemptMock($loginAttemptMock);

        $this->rateLimiter->recordAttempt('testuser', '192.168.1.1', true);
    }

    public function testRecordAttemptDelegatesFailedAttempt(): void
    {
        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('record')
            ->with('testuser', '192.168.1.1', false);

        $this->setLoginAttemptMock($loginAttemptMock);

        $this->rateLimiter->recordAttempt('testuser', '192.168.1.1', false);
    }

    public function testConstructorUsesDefaultValues(): void
    {
        $defaultLimiter = new RateLimiter();

        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('countFailedByIp')
            ->with('127.0.0.1', 900)
            ->willReturn(4);

        $reflection = new \ReflectionProperty(RateLimiter::class, 'loginAttempt');
        $reflection->setAccessible(true);
        $reflection->setValue($defaultLimiter, $loginAttemptMock);

        $this->assertFalse($defaultLimiter->isLockedOut('127.0.0.1'));
    }

    public function testConstructorUsesCustomValues(): void
    {
        $customLimiter = new RateLimiter(10, 1800);

        $loginAttemptMock = $this->createMock(LoginAttempt::class);
        $loginAttemptMock->expects($this->once())
            ->method('countFailedByIp')
            ->with('127.0.0.1', 1800)
            ->willReturn(9);

        $reflection = new \ReflectionProperty(RateLimiter::class, 'loginAttempt');
        $reflection->setAccessible(true);
        $reflection->setValue($customLimiter, $loginAttemptMock);

        $this->assertFalse($customLimiter->isLockedOut('127.0.0.1'));
    }

    private function setLoginAttemptMock(LoginAttempt $mock): void
    {
        $reflection = new \ReflectionProperty(RateLimiter::class, 'loginAttempt');
        $reflection->setAccessible(true);
        $reflection->setValue($this->rateLimiter, $mock);
    }
}
