<?php

namespace Tests\Models;

use PhpAuth\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    public function testTableNameIsPublicUsers(): void
    {
        $reflection = new \ReflectionProperty(User::class, 'TABLE_NAME');
        $reflection->setAccessible(true);

        $this->assertSame('public.users', $reflection->getValue($this->user));
    }

    public function testSetRequestStoresValues(): void
    {
        $data = ['username' => 'test', 'email' => 'test@example.com'];
        $this->user->setRequest($data);

        $this->assertSame($data, $this->user->getData());
    }

    public function testGetDataReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->user->getData());
    }

    public function testGetDataReturnsStoredValues(): void
    {
        $data = ['username' => 'juan', 'email' => 'juan@example.com'];
        $this->user->setRequest($data);

        $result = $this->user->getData();

        $this->assertSame('juan', $result['username']);
        $this->assertSame('juan@example.com', $result['email']);
    }

    public function testFindByUsernameReturnsNullWhenNoDatabase(): void
    {
        $result = $this->user->findByUsername('anyuser');

        $this->assertNull($result);
    }

    public function testFindByEmailReturnsNullWhenNoDatabase(): void
    {
        $result = $this->user->findByEmail('any@example.com');

        $this->assertNull($result);
    }

    public function testHasPermissionReturnsFalseWhenNoDatabase(): void
    {
        $result = $this->user->hasPermission(1, 'view_dashboard');

        $this->assertFalse($result);
    }

    public function testCreateUserReturnsZeroWhenNoDatabase(): void
    {
        $result = $this->user->createUser('test', 'test@example.com', 'hash', 1);

        $this->assertSame(0, $result);
    }

    public function testUpdateUserReturnsVoidWhenNoDatabase(): void
    {
        $this->expectNotToPerformAssertions();
        $this->user->updateUser(1, ['username' => 'test']);
    }

    public function testUpdatePasswordReturnsVoidWhenNoDatabase(): void
    {
        $this->expectNotToPerformAssertions();
        $this->user->updatePassword(1, 'newhash');
    }
}
