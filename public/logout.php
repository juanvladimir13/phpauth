<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../auth/session.php';

use PhpAuth\AuthRbac;

$manager = AuthRbac::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!AuthRbac::csrfVerify($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        $headers = getallheaders();
        if (str_contains($headers['Accept'] ?? '', 'application/json')) {
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }
        header('Location: /login.php');
        exit;
    }

    $manager->auth()->logout();

    $headers = getallheaders();
    if (str_contains($headers['Accept'] ?? '', 'application/json')) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Sesión cerrada exitosamente']);
        exit;
    }

    header('Location: login.php');
    exit;
}

// GET request — redirect to login
header('Location: login.php');
exit;
