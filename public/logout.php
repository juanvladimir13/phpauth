<?php

require '../vendor/autoload.php';

use App\Controllers\Auth;

$auth = new Auth();
$auth->logout();

$headers = getallheaders();
if (str_contains($headers['Accept'] ?? '', 'application/json')) {
    echo json_encode(['message' => 'Sesión cerrada exitosamente']);
    exit;
}

header('Location: login.php');
exit;
