<?php

namespace Tests\Controllers;

use App\Controllers\Csrf;
use PHPUnit\Framework\TestCase;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testGenerateTokenCreatesTokenInSession(): void
    {
        $token = Csrf::generateToken();

        $this->assertNotEmpty($token);
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function testGenerateTokenReusesExistingToken(): void
    {
        $token1 = Csrf::generateToken();
        $token2 = Csrf::generateToken();

        $this->assertSame($token1, $token2);
    }

    public function testVerifyTokenReturnsTrueForValidToken(): void
    {
        $token = Csrf::generateToken();

        $this->assertTrue(Csrf::verifyToken($token));
    }

    public function testVerifyTokenReturnsFalseForInvalidToken(): void
    {
        Csrf::generateToken();

        $this->assertFalse(Csrf::verifyToken('invalid-token'));
    }

    public function testVerifyTokenReturnsFalseForNullToken(): void
    {
        Csrf::generateToken();

        $this->assertFalse(Csrf::verifyToken(null));
    }

    public function testVerifyTokenReturnsFalseWhenNoSessionToken(): void
    {
        $this->assertFalse(Csrf::verifyToken('some-token'));
    }

    public function testVerifyTokenReturnsFalseForEmptyStringToken(): void
    {
        $this->assertFalse(Csrf::verifyToken(''));
    }

    public function testVerifyTokenUsesHashEquals(): void
    {
        $token = Csrf::generateToken();
        $this->assertTrue(Csrf::verifyToken($token));
    }
}
