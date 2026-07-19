<?php

require '../vendor/autoload.php';

use App\AuthRbac;

// Protegemos la página: Requiere estar logueado
$manager = AuthRbac::getInstance();
$manager->guard()->requireLogin();

// Puedes descomentar la siguiente línea para requerir un rol específico
// $manager->guard()->requireRole('admin');

// Requerimos el permiso granular 'view_dashboard'
$manager->guard()->requireCan('view_dashboard');

// Si la petición es API (Accept: application/json), devolvemos JSON
$headers = getallheaders();
if (str_contains($headers['Accept'] ?? '', 'application/json')) {
    echo json_encode([
        'message' => 'Bienvenido al Dashboard',
        'user' => [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ]
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Seguro</title>
</head>
<body>
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p>Tu rol actual es: <strong><?= htmlspecialchars($_SESSION['role'] ?? 'Sin rol') ?></strong></p>
    
    <p>Tienes acceso a esta página protegida porque posees el permiso: <code>view_dashboard</code>.</p>
    
    <a href="logout.php">Cerrar Sesión</a>
</body>
</html>
