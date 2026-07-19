<?php

/**
 * RBAC YAML Demo — Creación de usuarios con roles y permisos mediante YAML
 *
 * Uso: php examples/rbac_yaml_demo.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../auth/session.php';

use PhpAuth\AuthRbac;
use PhpAuth\Models\User;
use PhpAuth\Models\Role;

echo "=== RBAC YAML Demo: Roles y Permisos Granulares ===\n\n";

try {
    $manager = AuthRbac::getInstance();
    $manager->rbac()->seed();

    echo "--- 5. Matriz de permisos por usuario ---\n\n";

    $userModel = new User();
    $roleModel = new Role();

    // Obtener los usuarios de config para imprimir matriz
    $config = yaml_parse_file(__DIR__ . '/../auth/rbac.yml');
    $allPermissions = $config['permissions'] ?? [];

    printf("%-20s %-12s", "Usuario", "Rol");
    foreach ($allPermissions as $p) {
        printf(" %-18s", $p);
    }
    echo "\n";
    echo str_repeat('-', 20 + 12 + 19 * count($allPermissions)) . "\n";

    foreach ($config['users'] ?? [] as $cfg) {
        $user = $userModel->findByUsername($cfg['username']);
        if (!$user) {
            continue;
        }

        printf("%-20s %-12s", $user['username'], $user['role_name']);

        foreach ($allPermissions as $permName) {
            $has = $userModel->hasPermission((int)$user['id'], $permName);
            printf(" %-18s", $has ? '✔' : '—');
        }
        echo "\n";
    }

    echo "\n--- Demo completa ---\n";
} catch (\Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
