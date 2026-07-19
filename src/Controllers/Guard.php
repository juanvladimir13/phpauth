<?php

namespace PhpAuth\Controllers;

use PhpAuth\Models\User;

class Guard
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function requireLogin(string $redirectUrl = '/login'): void
    {
        if (empty($_SESSION['user_id'])) {
            if ($this->isApiRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                exit;
            }
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    public function requireRole(string $role, string $redirectUrl = '/'): void
    {
        $this->requireLogin($redirectUrl);

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

    public function requireCan(string $permission, string $redirectUrl = '/'): void
    {
        $this->requireLogin($redirectUrl);

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
