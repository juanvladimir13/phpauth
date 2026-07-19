<?php

require '../vendor/autoload.php';

use PhpAuth\AuthRbac;

$manager = AuthRbac::getInstance();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!AuthRbac::csrfVerify($_POST['csrf_token'] ?? '')) {
        die("Token CSRF inválido.");
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Por defecto asigamos el rol 'cliente' (id=2 en schema.sql). 
    // JAMAS confiar en el input para el role_id.
    $roleId = 2; 

    // Sanitización y Validación
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato de email no es válido.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        if ($manager->auth()->register($username, $email, $password, $roleId)) {
                $success = "Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
            } else {
                $error = "Ocurrió un error al registrar el usuario.";
            }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <h2>Registro</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <p><a href="login.php">Ir a Iniciar Sesión</a></p>
    <?php else: ?>
        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthRbac::csrfToken()) ?>">
            <label>Usuario: <input type="text" name="username" required></label><br><br>
            <label>Email: <input type="email" name="email" required></label><br><br>
            <label>Contraseña: <input type="password" name="password" required></label><br><br>
            <button type="submit">Registrarme</button>
        </form>
    <?php endif; ?>
</body>
</html>
