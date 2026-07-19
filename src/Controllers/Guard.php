<?php

namespace App\Controllers;

use App\Models\User;

class Guard
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            if ($this->isApiRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                exit;
            }
            header('Location: /login.php');
            exit;
        }
    }

    public function requireRole(string $role): void
    {
        $this->requireLogin();

        if (($_SESSION['role'] ?? '') !== $role) {
            http_response_code(403);
            if ($this->isApiRequest()) {
                echo json_encode(['error' => 'Acceso denegado. Se requiere rol: ' . $role]);
            } else {
                echo "Acceso Denegado: Requiere rol {$role}";
            }
            exit;
        }
    }

    public function requireCan(string $permission): void
    {
        $this->requireLogin();

        if (!$this->user->hasPermission((int)$_SESSION['user_id'], $permission)) {
            http_response_code(403);
            if ($this->isApiRequest()) {
                echo json_encode(['error' => 'Acceso denegado. Falta permiso']);
            } else {
                echo "Acceso Denegado: Falta el permiso {$permission}";
            }
            exit;
        }
    }

    private function isApiRequest(): bool
    {
        $headers = getallheaders();
        $accept = $headers['Accept'] ?? '';
        return str_contains($accept, 'application/json');
    }
}
