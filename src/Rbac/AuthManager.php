<?php

namespace PhpAuth\Rbac;

use PhpAuth\Models\Role;
use PhpAuth\Models\Permission;
use PhpAuth\Models\User;

class AuthManager
{
    private Role $roleModel;
    private Permission $permModel;
    private User $userModel;

    public function __construct()
    {
        $this->roleModel = new Role();
        $this->permModel = new Permission();
        $this->userModel = new User();
    }

    public function getRoleModel(): Role
    {
        return $this->roleModel;
    }

    public function getPermModel(): Permission
    {
        return $this->permModel;
    }

    public function getUserModel(): User
    {
        return $this->userModel;
    }

    public function seed(string $yamlFilePath = './../auth/rbac.yml'): void
    {
        if (!file_exists($yamlFilePath)) {
            throw new \InvalidArgumentException("El archivo YAML no existe: {$yamlFilePath}");
        }

        if (!function_exists('yaml_parse_file')) {
            throw new \RuntimeException("La extensión 'yaml' de PHP no está instalada.");
        }

        $config = yaml_parse_file($yamlFilePath);
        if ($config === false || !is_array($config)) {
            throw new \RuntimeException("No se pudo parsear el archivo YAML o el formato es incorrecto.");
        }

        $permIds = $this->seedPermissions($config['permissions'] ?? []);
        $roleIds = $this->seedRolesAndAssignPermissions($config['roles'] ?? [], $permIds);
        $this->seedUsers($config['users'] ?? [], $roleIds);
    }

    /**
     * @param array<int, string> $permissions
     * @return array<string, int>
     */
    private function seedPermissions(array $permissions): array
    {
        echo "--- 1. Creando permisos granulares ---\n";
        $permIds = [];
        foreach ($permissions as $name) {
            $existing = $this->permModel->findByName($name);
            if ($existing) {
                $permIds[$name] = (int)$existing['id'];
                echo "  [SKIP] Permiso '{$name}' ya existe (id={$existing['id']})\n";
            } else {
                $id = $this->permModel->create($name);
                $permIds[$name] = (int)$id;
                echo "  [CREADO] Permiso '{$name}' (id={$id})\n";
            }
        }
        echo "\n";
        return $permIds;
    }

    /**
     * @param array<string, array<int, string>> $rolesConfig
     * @param array<string, int> $permIds
     * @return array<string, int>
     */
    private function seedRolesAndAssignPermissions(array $rolesConfig, array $permIds): array
    {
        echo "--- 2. Creando roles ---\n";
        $roleIds = [];
        foreach ($rolesConfig as $roleName => $perms) {
            $existing = $this->roleModel->findByName($roleName);
            if ($existing) {
                $roleIds[$roleName] = (int)$existing['id'];
                echo "  [SKIP] Rol '{$roleName}' ya existe (id={$existing['id']})\n";
            } else {
                $id = $this->roleModel->create($roleName);
                $roleIds[$roleName] = (int)$id;
                echo "  [CREADO] Rol '{$roleName}' (id={$id})\n";
            }
        }
        echo "\n";

        echo "--- 3. Asignando permisos a roles ---\n";
        foreach ($rolesConfig as $roleName => $perms) {
            $roleId = $roleIds[$roleName];

            $currentPerms = $this->roleModel->getPermissions($roleId);
            $currentPermNames = array_column($currentPerms, 'name');

            foreach ($perms as $permName) {
                if (!isset($permIds[$permName])) {
                    echo "  [WARN] Permiso '{$permName}' no encontrado, se omite\n";
                    continue;
                }

                if (in_array($permName, $currentPermNames, true)) {
                    echo "  [SKIP] Rol '{$roleName}' ya tiene permiso '{$permName}'\n";
                } else {
                    $this->roleModel->assignPermission($roleId, $permIds[$permName]);
                    echo "  [ASIGNADO] Rol '{$roleName}' → permiso '{$permName}'\n";
                }
            }
        }
        echo "\n";

        return $roleIds;
    }

    /**
     * @param array<int, array<string, string>> $usersConfig
     * @param array<string, int> $roleIds
     */
    private function seedUsers(array $usersConfig, array $roleIds): void
    {
        echo "--- 4. Creando usuarios ---\n";
        foreach ($usersConfig as $cfg) {
            if (!isset($roleIds[$cfg['role']])) {
                echo "  [ERROR] Rol '{$cfg['role']}' no existe para el usuario '{$cfg['username']}'\n";
                continue;
            }

            $existingUser = $this->userModel->findByUsername($cfg['username']);
            if ($existingUser) {
                echo "  [SKIP] Usuario '{$cfg['username']}' ya existe (id={$existingUser['id']})\n";
                continue;
            }

            $hash = User::passwordToHash($cfg['password']);
            $userId = $this->userModel->createUser(
                $cfg['username'],
                $cfg['email'],
                $hash,
                $roleIds[$cfg['role']]
            );

            if ($userId !== 0) {
                echo "  [CREADO] Usuario '{$cfg['username']}' → rol '{$cfg['role']}' (id={$userId})\n";
            } else {
                echo "  [ERROR] No se pudo crear el usuario '{$cfg['username']}'\n";
            }
        }
        echo "\n";
    }
}
