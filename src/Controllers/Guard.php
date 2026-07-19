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
            $redirectUrl = $this->sanitizeRedirectUrl($redirectUrl, '/login');
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    public function requireRole(string $role, string $redirectUrl = '/'): void
    {
        $this->requireLogin($redirectUrl);

        if (($_SESSION['role'] ?? '') !== $role) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado. Se requiere el rol: ' . htmlspecialchars($role)]);
                exit;
            }
            $redirectUrl = $this->sanitizeRedirectUrl($redirectUrl, '/');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function requireCan(string $permission, string $redirectUrl = '/'): void
    {
        $this->requireLogin($redirectUrl);

        if (!$this->user->hasPermission((int)($_SESSION['user_id'] ?? 0), $permission)) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado. Falta permiso']);
                exit;
            }
            $redirectUrl = $this->sanitizeRedirectUrl($redirectUrl, '/');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    private function sanitizeRedirectUrl(string $url, string $default): string
    {
        if (preg_match('#^/[a-zA-Z0-9._/-]*$#', $url)) {
            return $url;
        }
        return $default;
    }

    private function isApiRequest(): bool
    {
        $headers = getallheaders();
        $accept = $headers['Accept'] ?? '';
        return str_contains($accept, 'application/json');
    }
}
