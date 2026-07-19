<?php

require '../vendor/autoload.php';

use App\Controllers\Auth;
use App\Controllers\Csrf;
use App\Controllers\RateLimiter;

$rateLimiter = new RateLimiter();
$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protección CSRF
    if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido. Posible ataque detectado.");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];

    if (empty($username) || empty($password)) {
        $error = "Usuario y contraseña son obligatorios.";
    } elseif ($rateLimiter->isLockedOut($ip)) {
        $error = "Demasiados intentos fallidos. Por favor, intenta más tarde.";
    } else {
        if ($auth->login($username, $password)) {
            $rateLimiter->recordAttempt($username, $ip, true);
            header('Location: dashboard.php');
            exit;
        } else {
            $rateLimiter->recordAttempt($username, $ip, false);
            // Mensaje genérico, no revelamos si el usuario existe o no
            $error = "Credenciales incorrectas.";
        }
    }
}

// Respuesta para API
$headers = getallheaders();
if (str_contains($headers['Accept'] ?? '', 'application/json') && $error) {
    http_response_code(401);
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
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
        <label>Usuario: <input type="text" name="username" required></label><br><br>
        <label>Contraseña: <input type="password" name="password" required></label><br><br>
        <button type="submit">Ingresar</button>
    </form>
    <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
</body>
</html>
