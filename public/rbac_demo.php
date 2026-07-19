<?php
/**
 * RBAC Demo — Creación de usuarios con roles y permisos granulares
 *
 * Uso: php examples/rbac_demo.php
 *
 * Requiere conexión a PostgreSQL con el schema ejecutado (docs/schema.sql).
 * Es seguro ejecutarlo múltiples veces (usa findByName para evitar duplicados).
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/session.php';
require __DIR__ . '/../config/database.php';

use PhpAuth\Models\Role;
use PhpAuth\Models\Permission;
use PhpAuth\Models\User;

$roleModel = new Role();
$permModel = new Permission();
$userModel = new User();

echo "=== RBAC Demo: Roles y Permisos Granulares ===\n\n";

// ──────────────────────────────────────────────
// 1. Definir permisos granulares
// ──────────────────────────────────────────────
$granularPermissions = [
    'create_post',
    'edit_post',
    'delete_post',
    'publish_post',
    'view_reports',
    'export_data',
];

echo "--- 1. Creando permisos granulares ---\n";

$permIds = [];
foreach ($granularPermissions as $name) {
    $existing = $permModel->findByName($name);
    if ($existing) {
        $permIds[$name] = $existing['id'];
        echo "  [SKIP] Permiso '{$name}' ya existe (id={$existing['id']})\n";
    } else {
        $id = $permModel->create($name);
        $permIds[$name] = $id;
        echo "  [CREADO] Permiso '{$name}' (id={$id})\n";
    }
}

// También necesitamos el permiso existente 'view_dashboard'
$viewDashboard = $permModel->findByName('view_dashboard');
if ($viewDashboard) {
    $permIds['view_dashboard'] = $viewDashboard['id'];
}

echo "\n";

// ──────────────────────────────────────────────
// 2. Definir roles y sus permisos
// ──────────────────────────────────────────────
$rolesConfig = [
    'editor' => [
        'create_post',
        'edit_post',
        'publish_post',
        'view_dashboard',
    ],
    'author' => [
        'create_post',
        'edit_post',
        'view_dashboard',
    ],
    'analyst' => [
        'view_reports',
        'export_data',
    ],
];

echo "--- 2. Creando roles ---\n";

$roleIds = [];
foreach ($rolesConfig as $roleName => $perms) {
    $existing = $roleModel->findByName($roleName);
    if ($existing) {
        $roleIds[$roleName] = $existing['id'];
        echo "  [SKIP] Rol '{$roleName}' ya existe (id={$existing['id']})\n";
    } else {
        $id = $roleModel->create($roleName);
        $roleIds[$roleName] = $id;
        echo "  [CREADO] Rol '{$roleName}' (id={$id})\n";
    }
}

echo "\n";

// ──────────────────────────────────────────────
// 3. Asignar permisos a roles
// ──────────────────────────────────────────────
echo "--- 3. Asignando permisos a roles ---\n";

foreach ($rolesConfig as $roleName => $perms) {
    $roleId = $roleIds[$roleName];

    // Obtener permisos actuales del rol para evitar duplicados
    $currentPerms = $roleModel->getPermissions($roleId);
    $currentPermNames = array_column($currentPerms, 'name');

    foreach ($perms as $permName) {
        if (!isset($permIds[$permName])) {
            echo "  [WARN] Permiso '{$permName}' no encontrado, se omite\n";
            continue;
        }

        if (in_array($permName, $currentPermNames, true)) {
            echo "  [SKIP] Rol '{$roleName}' ya tiene permiso '{$permName}'\n";
        } else {
            $roleModel->assignPermission($roleId, $permIds[$permName]);
            echo "  [ASIGNADO] Rol '{$roleName}' → permiso '{$permName}'\n";
        }
    }
}

echo "\n";

// ──────────────────────────────────────────────
// 4. Crear usuarios de ejemplo
// ──────────────────────────────────────────────
$usersConfig = [
    [
        'username' => 'juan_admin',
        'email'    => 'juan_admin@example.com',
        'password' => 'password123',
        'role'     => 'admin',
    ],
    [
        'username' => 'ana_editor',
        'email'    => 'ana_editor@example.com',
        'password' => 'password123',
        'role'     => 'editor',
    ],
    [
        'username' => 'luis_author',
        'email'    => 'luis_author@example.com',
        'password' => 'password123',
        'role'     => 'author',
    ],
    [
        'username' => 'carlos_analyst',
        'email'    => 'carlos_analyst@example.com',
        'password' => 'password123',
        'role'     => 'analyst',
    ],
];

echo "--- 4. Creando usuarios ---\n";

$createdUsers = [];
foreach ($usersConfig as $cfg) {
    $role = $roleModel->findByName($cfg['role']);
    if (!$role) {
        echo "  [ERROR] Rol '{$cfg['role']}' no existe en la BD\n";
        continue;
    }

    $existingUser = $userModel->findByUsername($cfg['username']);
    if ($existingUser) {
        $createdUsers[] = $existingUser;
        echo "  [SKIP] Usuario '{$cfg['username']}' ya existe (id={$existingUser['id']})\n";
        continue;
    }

    $hash = password_hash($cfg['password'], PASSWORD_ARGON2ID);
    $userId = $userModel->createUser(
        $cfg['username'],
        $cfg['email'],
        $hash,
        (int)$role['id']
    );

    if ($userId !== 0) {
        $userData = $userModel->findByUsername($cfg['username']);
        $createdUsers[] = $userData;
        echo "  [CREADO] Usuario '{$cfg['username']}' → rol '{$cfg['role']}' (id={$userId})\n";
    } else {
        echo "  [ERROR] No se pudo crear el usuario '{$cfg['username']}'\n";
    }
}

echo "\n";

// ──────────────────────────────────────────────
// 5. Verificar permisos — matriz usuario x permiso
// ──────────────────────────────────────────────
echo "--- 5. Matriz de permisos por usuario ---\n\n";

$allPermissions = array_merge(
    $granularPermissions,
    ['view_dashboard', 'manage_users', 'manage_roles']
);

// Encabezado de la tabla
printf("%-20s %-12s", "Usuario", "Rol");
foreach ($allPermissions as $p) {
    printf(" %-18s", $p);
}
echo "\n";
echo str_repeat('-', 20 + 12 + 19 * count($allPermissions)) . "\n";

foreach ($createdUsers as $user) {
    $roleName = $roleModel->findByName($user['role_name']);

    printf("%-20s %-12s", $user['username'], $user['role_name']);

    foreach ($allPermissions as $permName) {
        $has = $userModel->hasPermission((int)$user['id'], $permName);
        printf(" %-18s", $has ? '✔' : '—');
    }
    echo "\n";
}

echo "\n--- Demo completa ---\n";
