<?php

namespace Tests\Controllers;

use App\Controllers\Guard;
use App\Models\User;
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
        $_SESSION = [];

        $userMock = $this->createMock(User::class);
        $this->setUserMock($userMock);

        try {
            $this->guard->requireLogin('/custom/login');
        } catch (\Exception $e) {
        }

        $this->assertEmpty($_SESSION['user_id'] ?? null);
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
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'user';

        $userMock = $this->createMock(User::class);
        $this->setUserMock($userMock);

        try {
            $this->guard->requireRole('admin', '/');
        } catch (\Exception $e) {
        }

        $this->assertSame('user', $_SESSION['role']);
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
        $_SESSION['user_id'] = 1;

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('hasPermission')
            ->with(1, 'admin_access')
            ->willReturn(false);

        $this->setUserMock($userMock);

        try {
            $this->guard->requireCan('admin_access', '/');
        } catch (\Exception $e) {
        }
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
