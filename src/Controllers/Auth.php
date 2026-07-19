<?php

namespace PhpAuth\Controllers;

use PhpAuth\Models\User;

class Auth
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->user->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }

    public function register(string $username, string $email, string $password, int $roleId): bool
    {
        $hash = password_hash($password, PASSWORD_ARGON2ID);

        return $this->user->createUser($username, $email, $hash, $roleId) !== 0;
    }
}
