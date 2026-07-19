<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../auth/session.php';

use PhpAuth\AuthRbac;

$manager = AuthRbac::getInstance();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protección CSRF
    if (!AuthRbac::csrfVerify($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido. Posible ataque detectado.");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];

    if (empty($username) || empty($password)) {
        $error = "Usuario y contraseña son obligatorios.";
    } elseif ($manager->rateLimiter()->isLockedOut($ip)) {
        $error = "Demasiados intentos fallidos. Por favor, intenta más tarde.";
    } elseif ($manager->rateLimiter()->isUserLockedOut($username)) {
        $error = "Demasiados intentos fallidos para este usuario. Por favor, intenta más tarde.";
    } else {
        if ($manager->auth()->login($username, $password)) {
            $manager->rateLimiter()->recordAttempt($username, $ip, true);
            header('Location: dashboard.php');
            exit;
        } else {
            $manager->rateLimiter()->recordAttempt($username, $ip, false);
            // Mensaje genérico, no revelamos si el usuario existe o no
            $error = "Credenciales incorrectas.";
        }
    }
}

// Respuesta para API
$headers = getallheaders();
if (str_contains($headers['Accept'] ?? '', 'application/json') && $error) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => $error]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <?php if ($error): ?><p style="color:red;">
        <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthRbac::csrfToken()) ?>">
        <label>Usuario: <input type="text" name="username" required></label><br><br>
        <label>Contraseña: <input type="password" name="password" required></label><br><br>
        <button type="submit">Ingresar</button>
    </form>
    <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
</body>
</html>
