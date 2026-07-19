<?php

namespace Tests\Models;

use PhpAuth\Models\Permission;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    private Permission $permission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permission = new Permission();
    }

    public function testTableNameIsPublicPermissions(): void
    {
        $reflection = new \ReflectionProperty(Permission::class, 'TABLE_NAME');
        $reflection->setAccessible(true);

        $this->assertSame('public.permissions', $reflection->getValue($this->permission));
    }

    public function testSetRequestStoresValues(): void
    {
        $data = ['name' => 'view_dashboard'];
        $this->permission->setRequest($data);

        $this->assertSame($data, $this->permission->getData());
    }

    public function testGetDataReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->permission->getData());
    }

    public function testFindByNameReturnsNullWhenNoDatabase(): void
    {
        $result = $this->permission->findByName('view_dashboard');

        $this->assertNull($result);
    }

    public function testCreateReturnsZeroWhenNoDatabase(): void
    {
        $result = $this->permission->create('view_dashboard');

        $this->assertSame(0, $result);
    }
}
