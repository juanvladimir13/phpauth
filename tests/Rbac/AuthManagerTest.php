<?php

namespace Tests\Rbac;

use PhpAuth\Models\Permission;
use PhpAuth\Models\Role;
use PhpAuth\Models\User;
use PhpAuth\Rbac\AuthManager;
use PHPUnit\Framework\TestCase;

class AuthManagerTest extends TestCase
{
    private AuthManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new AuthManager();
    }

    public function testGetRoleModelReturnsRoleInstance(): void
    {
        $roleMock = $this->createMock(Role::class);
        $this->setModelMock('roleModel', $roleMock);

        $this->assertSame($roleMock, $this->manager->getRoleModel());
    }

    public function testGetPermModelReturnsPermissionInstance(): void
    {
        $permMock = $this->createMock(Permission::class);
        $this->setModelMock('permModel', $permMock);

        $this->assertSame($permMock, $this->manager->getPermModel());
    }

    public function testGetUserModelReturnsUserInstance(): void
    {
        $userMock = $this->createMock(User::class);
        $this->setModelMock('userModel', $userMock);

        $this->assertSame($userMock, $this->manager->getUserModel());
    }

    public function testSeedThrowsExceptionWhenYamlFileNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El archivo YAML no existe');

        $this->manager->seed('non_existent.yml');
    }

    public function testSeedThrowsExceptionWhenYamlParseFails(): void
    {
        if (!function_exists('yaml_parse_file')) {
            $this->markTestSkipped('yaml extension not installed');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'invalid_yaml');
        file_put_contents($tempFile, "invalid: \t yaml: { content");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se pudo parsear el archivo YAML o el formato es incorrecto.');

        try {
            @$this->manager->seed($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    public function testGetRoleModelReturnsRoleByDefault(): void
    {
        $this->assertInstanceOf(Role::class, $this->manager->getRoleModel());
    }

    public function testGetPermModelReturnsPermissionByDefault(): void
    {
        $this->assertInstanceOf(Permission::class, $this->manager->getPermModel());
    }

    public function testGetUserModelReturnsUserByDefault(): void
    {
        $this->assertInstanceOf(User::class, $this->manager->getUserModel());
    }

    private function setModelMock(string $property, object $mock): void
    {
        $reflection = new \ReflectionProperty(AuthManager::class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($this->manager, $mock);
    }
}
