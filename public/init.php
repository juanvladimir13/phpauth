<?php
// public/init.php

// Autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar .env de forma muy simple (parse_ini_file) si existe
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    if ($env) {
        foreach ($env as $k => $v) {
            putenv("$k=$v");
        }
    }
}

// Inicialización de la sesión segura
require_once __DIR__ . '/../config/session.php';

// Conexión a la base de datos
$pdo = require __DIR__ . '/../config/database.php';

// Instanciar servicios
$auth = new \App\Auth($pdo);
$guard = new \App\Guard($pdo);
$rateLimiter = new \App\RateLimiter($pdo);

// Helper function getallheaders no existe de forma nativa en PHP CLI server aveces
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
