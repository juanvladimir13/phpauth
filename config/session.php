<?php
// config/session.php

$timeout = getenv('SESSION_TIMEOUT') ?: 3600; // 1 hora por defecto

ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0, // La cookie dura hasta cerrar el navegador, pero la sesión expira en el servidor
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // true si usas HTTPS (recomendado en producción)
    'httponly' => true, // Mitiga XSS
    'samesite' => 'Lax' // Mitiga CSRF
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inactividad
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    session_start(); // Iniciar una nueva limpia
}
$_SESSION['last_activity'] = time();

// Session fixation mitigation: regenerar ID
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // Cada 5 min
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
