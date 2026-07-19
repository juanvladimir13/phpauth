<?php

namespace Tests\Models;

use PhpAuth\Models\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = new Role();
    }

    public function testTableNameIsPublicRoles(): void
    {
        $reflection = new \ReflectionProperty(Role::class, 'TABLE_NAME');
        $reflection->setAccessible(true);

        $this->assertSame('phpauth.roles', $reflection->getValue($this->role));
    }

    public function testSetRequestStoresValues(): void
    {
        $data = ['name' => 'admin'];
        $this->role->setRequest($data);

        $this->assertSame($data, $this->role->getData());
    }

    public function testGetDataReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->role->getData());
    }

    public function testGetDataReturnsStoredValues(): void
    {
        $data = ['name' => 'editor'];
        $this->role->setRequest($data);

        $this->assertSame('editor', $this->role->getData()['name']);
    }

    public function testFindByNameReturnsNullWhenNoDatabase(): void
    {
        $result = $this->role->findByName('admin');

        $this->assertNull($result);
    }

    public function testCreateReturnsZeroWhenNoDatabase(): void
    {
        $result = $this->role->create('admin');

        $this->assertSame(0, $result);
    }

    public function testGetPermissionsReturnsEmptyArrayWhenNoDatabase(): void
    {
        $result = $this->role->getPermissions(1);

        $this->assertSame([], $result);
    }

    public function testAssignPermissionReturnsVoidWhenNoDatabase(): void
    {
        $this->expectNotToPerformAssertions();
        $this->role->assignPermission(1, 1);
    }
}
