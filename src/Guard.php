<?php
namespace App;

use PDO;

class Guard
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            // Retornamos 401 para la API, o redireccionamos en web normal
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

        if (!$this->userCan($_SESSION['user_id'], $permission)) {
            http_response_code(403);
            if ($this->isApiRequest()) {
                echo json_encode(['error' => 'Acceso denegado. Falta permiso']);
            } else {
                echo "Acceso Denegado: Falta el permiso {$permission}";
            }
            exit;
        }
    }

    private function userCan(int $userId, string $permission): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM users u
            JOIN role_permissions rp ON u.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE u.id = :user_id AND p.name = :permission
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':permission' => $permission
        ]);
        
        return (bool) $stmt->fetchColumn();
    }
    
    private function isApiRequest(): bool
    {
        $headers = getallheaders();
        $accept = $headers['Accept'] ?? '';
        return str_contains($accept, 'application/json');
    }
}
