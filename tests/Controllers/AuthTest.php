<?php

namespace Tests\Controllers;

use PhpAuth\Controllers\Auth;
use PhpAuth\Models\User;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];

        $this->auth = new Auth();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testLogoutClearsSessionAndDestroys(): void
    {
        session_start();
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';

        $this->auth->logout();

        $this->assertEmpty($_SESSION);
    }

    public function testLoginReturnsFalseWhenUserNotFound(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('findByUsername')
            ->with('nonexistent')
            ->willReturn(null);

        $this->setUserMock($userMock);

        $this->assertFalse($this->auth->login('nonexistent', 'password'));
    }

    public function testLoginReturnsFalseWithWrongPassword(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('findByUsername')
            ->with('testuser')
            ->willReturn([
                'id' => 1,
                'username' => 'testuser',
                'password_hash' => password_hash('correct', PASSWORD_ARGON2ID),
                'role_name' => 'admin',
            ]);

        $this->setUserMock($userMock);

        $this->assertFalse($this->auth->login('testuser', 'wrong'));
    }

    public function testLoginReturnsTrueWithValidCredentials(): void
    {
        session_start();
        $password = 'correct-password';

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('findByUsername')
            ->with('testuser')
            ->willReturn([
                'id' => 1,
                'username' => 'testuser',
                'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
                'role_name' => 'admin',
            ]);

        $this->setUserMock($userMock);

        $result = $this->auth->login('testuser', $password);

        $this->assertTrue($result);
        $this->assertSame(1, $_SESSION['user_id']);
        $this->assertSame('testuser', $_SESSION['username']);
        $this->assertSame('admin', $_SESSION['role']);
    }

    public function testRegisterDelegatesToUserModel(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('createUser')
            ->with(
                $this->equalTo('newuser'),
                $this->equalTo('new@example.com'),
                $this->callback(function ($hash) {
                    return password_verify('secret123', $hash);
                }),
                $this->equalTo(2)
            )
            ->willReturn(5);

        $this->setUserMock($userMock);

        $result = $this->auth->register('newuser', 'new@example.com', 'secret123', 2);

        $this->assertTrue($result);
    }

    public function testRegisterReturnsFalseWhenUserCreationFails(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('createUser')
            ->willReturn(0);

        $this->setUserMock($userMock);

        $result = $this->auth->register('failuser', 'fail@example.com', 'secret123', 1);

        $this->assertFalse($result);
    }

    public function testUpdateUserReturnsFalseWhenDataIsEmpty(): void
    {
        $result = $this->auth->updateUser(1, ['invalid_field' => 'val']);
        $this->assertFalse($result);
    }

    public function testUpdateUserReturnsFalseWhenUserNotFound(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn([]);

        $this->setUserMock($userMock);

        $result = $this->auth->updateUser(1, ['username' => 'newname']);
        $this->assertFalse($result);
    }

    public function testUpdateUserReturnsTrueWhenUserUpdated(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(['id' => 1, 'username' => 'oldname']);
        $userMock->expects($this->once())
            ->method('updateUser')
            ->with(1, ['username' => 'newname']);

        $this->setUserMock($userMock);

        $result = $this->auth->updateUser(1, ['username' => 'newname']);
        $this->assertTrue($result);
    }

    public function testUpdatePasswordReturnsFalseWhenUserNotFound(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn([]);

        $this->setUserMock($userMock);

        $result = $this->auth->updatePassword(1, 'old', 'new');
        $this->assertFalse($result);
    }

    public function testUpdatePasswordReturnsFalseWhenOldPasswordIncorrect(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(['id' => 1, 'password_hash' => password_hash('correct', PASSWORD_ARGON2ID)]);

        $this->setUserMock($userMock);

        $result = $this->auth->updatePassword(1, 'wrong', 'new');
        $this->assertFalse($result);
    }

    public function testUpdatePasswordReturnsTrueWhenPasswordUpdated(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(['id' => 1, 'password_hash' => password_hash('old', PASSWORD_ARGON2ID)]);
        $userMock->expects($this->once())
            ->method('updatePassword')
            ->with($this->equalTo(1), $this->callback(function ($hash) {
                return password_verify('new', $hash);
            }));

        $this->setUserMock($userMock);

        $result = $this->auth->updatePassword(1, 'old', 'new');
        $this->assertTrue($result);
    }

    private function setUserMock(User $mock): void
    {
        $reflection = new \ReflectionProperty(Auth::class, 'user');
        $reflection->setAccessible(true);
        $reflection->setValue($this->auth, $mock);
    }
}
