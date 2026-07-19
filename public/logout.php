<?php

require '../vendor/autoload.php';

use App\AuthRbac;

$manager = AuthRbac::getInstance();
$manager->auth()->logout();

$headers = getallheaders();
if (str_contains($headers['Accept'] ?? '', 'application/json')) {
    echo json_encode(['message' => 'Sesión cerrada exitosamente']);
    exit;
}

header('Location: login.php');
exit;
