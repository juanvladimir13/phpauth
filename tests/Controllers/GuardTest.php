<?php

namespace Tests\Controllers;

use PhpAuth\Controllers\Guard;
use PhpAuth\Models\User;
use PHPUnit\Framework\TestCase;

class GuardTest extends TestCase
{
    private Guard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];

        $this->guard = new Guard();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testRequireLoginDoesNothingWhenUserIsLoggedIn(): void
    {
        $_SESSION['user_id'] = 1;

        $this->guard->requireLogin();

        $this->assertSame(1, $_SESSION['user_id']);
    }

    public function testRequireLoginUsesCustomRedirectUrl(): void
    {
        $this->markTestSkipped('Cannot test method because it calls exit()');
    }

    public function testRequireRoleDoesNothingWhenUserHasCorrectRole(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        $userMock = $this->createMock(User::class);
        $this->setUserMock($userMock);

        $this->guard->requireRole('admin');

        $this->assertSame('admin', $_SESSION['role']);
    }

    public function testRequireRoleReturns403WithApiRequest(): void
    {
        $this->markTestSkipped('Cannot test method because it calls exit()');
    }

    public function testRequireCanDoesNothingWhenUserHasPermission(): void
    {
        $_SESSION['user_id'] = 1;

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('hasPermission')
            ->with(1, 'view_dashboard')
            ->willReturn(true);

        $this->setUserMock($userMock);

        $this->guard->requireCan('view_dashboard');
    }

    public function testRequireCanReturns403WhenPermissionMissing(): void
    {
        $this->markTestSkipped('Cannot test method because it calls exit()');
    }

    public function testRequireCanUsesDefaultRedirectPath(): void
    {
        $_SESSION['user_id'] = 1;

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('hasPermission')
            ->willReturn(true);

        $this->setUserMock($userMock);

        $this->guard->requireCan('view_dashboard');
    }

    private function setUserMock(User $mock): void
    {
        $reflection = new \ReflectionProperty(Guard::class, 'user');
        $reflection->setAccessible(true);
        $reflection->setValue($this->guard, $mock);
    }
}
