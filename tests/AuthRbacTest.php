<?php

namespace Tests;

use App\AuthRbac;
use App\Controllers\Auth;
use App\Controllers\Csrf;
use App\Controllers\Guard;
use App\Controllers\RateLimiter;
use App\Rbac\AuthManager;
use PHPUnit\Framework\TestCase;

class AuthRbacTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AuthRbac::resetInstance();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        AuthRbac::resetInstance();
        $_SESSION = [];
        parent::tearDown();
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $instance1 = AuthRbac::getInstance();
        $instance2 = AuthRbac::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testResetInstanceClearsSingleton(): void
    {
        $instance1 = AuthRbac::getInstance();
        AuthRbac::resetInstance();
        $instance2 = AuthRbac::getInstance();

        $this->assertNotSame($instance1, $instance2);
    }

    public function testAuthReturnsAuthController(): void
    {
        $manager = AuthRbac::getInstance();

        $this->assertInstanceOf(Auth::class, $manager->auth());
    }

    public function testAuthIsLazyLoadedAndCached(): void
    {
        $manager = AuthRbac::getInstance();
        $auth1 = $manager->auth();
        $auth2 = $manager->auth();

        $this->assertSame($auth1, $auth2);
    }

    public function testGuardReturnsGuardController(): void
    {
        $manager = AuthRbac::getInstance();

        $this->assertInstanceOf(Guard::class, $manager->guard());
    }

    public function testGuardIsLazyLoadedAndCached(): void
    {
        $manager = AuthRbac::getInstance();
        $guard1 = $manager->guard();
        $guard2 = $manager->guard();

        $this->assertSame($guard1, $guard2);
    }

    public function testCsrfTokenDelegatesToCsrf(): void
    {
        $token = AuthRbac::csrfToken();

        $this->assertNotEmpty($token);
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function testCsrfVerifyDelegatesToCsrf(): void
    {
        $token = AuthRbac::csrfToken();

        $this->assertTrue(AuthRbac::csrfVerify($token));
        $this->assertFalse(AuthRbac::csrfVerify('bad-token'));
        $this->assertFalse(AuthRbac::csrfVerify(null));
    }

    public function testRateLimiterReturnsRateLimiter(): void
    {
        $manager = AuthRbac::getInstance();

        $this->assertInstanceOf(RateLimiter::class, $manager->rateLimiter());
    }

    public function testRateLimiterIsLazyLoadedAndCached(): void
    {
        $manager = AuthRbac::getInstance();
        $rl1 = $manager->rateLimiter();
        $rl2 = $manager->rateLimiter();

        $this->assertSame($rl1, $rl2);
    }

    public function testRateLimiterAcceptsParametersOnFirstCall(): void
    {
        $manager = AuthRbac::getInstance();
        $rateLimiter = $manager->rateLimiter(10, 1800);

        $this->assertInstanceOf(RateLimiter::class, $rateLimiter);
    }

    public function testRbacReturnsAuthManager(): void
    {
        $manager = AuthRbac::getInstance();

        $this->assertInstanceOf(AuthManager::class, $manager->rbac());
    }

    public function testRbacIsLazyLoadedAndCached(): void
    {
        $manager = AuthRbac::getInstance();
        $rbac1 = $manager->rbac();
        $rbac2 = $manager->rbac();

        $this->assertSame($rbac1, $rbac2);
    }

    public function testWakeupThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se puede deserializar un singleton.');

        $manager = AuthRbac::getInstance();
        $manager->__wakeup();
    }

    public function testAllComponentsAreDifferentInstances(): void
    {
        $manager = AuthRbac::getInstance();

        $this->assertNotSame($manager->auth(), $manager->guard());
        $this->assertNotSame($manager->auth(), $manager->rateLimiter());
        $this->assertNotSame($manager->auth(), $manager->rbac());
        $this->assertNotSame($manager->guard(), $manager->rateLimiter());
        $this->assertNotSame($manager->guard(), $manager->rbac());
        $this->assertNotSame($manager->rateLimiter(), $manager->rbac());
    }
}
