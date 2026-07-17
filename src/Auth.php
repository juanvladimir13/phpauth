<?php
namespace App;

use PDO;

class Auth
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(string $username, string $password): bool
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.username = :username
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // PASSWORD_ARGON2ID y verify resuelven el password
        if ($user && password_verify($password, $user['password_hash'])) {
            // Protección contra Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            return true;
        }

        // Mensaje genérico de error (controlado por quien llama, acá solo retorna false)
        return false;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        
        // Destruir la cookie de sesión completamente
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    public function register(string $username, string $email, string $password, int $roleId): bool
    {
        // PASSWORD_ARGON2ID es recomendado sobre DEFAULT para mayor seguridad
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash, role_id) 
            VALUES (:username, :email, :password_hash, :role_id)
        ");
        
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $hash,
            ':role_id' => $roleId
        ]);
    }
}
