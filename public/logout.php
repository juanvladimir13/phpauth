<?php
require_once __DIR__ . '/init.php';

$auth->logout();

$headers = getallheaders();
if (str_contains($headers['Accept'] ?? '', 'application/json')) {
    echo json_encode(['message' => 'Sesión cerrada exitosamente']);
    exit;
}

header('Location: login.php');
exit;
