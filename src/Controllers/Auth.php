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

        if ($user && User::passwordHashVerify($password, $user['password_hash'])) {
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
        $hash = User::passwordToHash($password);
        return $this->user->createUser($username, $email, $hash, $roleId) !== 0;
    }

    public function updateUser(int $userId, array $data): bool
    {
        $user = $this->user->find($userId);
        if ($user === null) {
            return false;
        }

        $user->setRequest($data);
        return $this->user->update($userId, $user->getData());
    }

    public function updatePassword(int $userId, string $passwordOld, string $passwordNew): bool
    {
        $user = $this->user->find($userId);

        if ($user === null || !User::passwordHashVerify($passwordOld, $user['password_hash'])) {
            return false;
        }

        $newHash = User::passwordToHash($passwordNew);
        return $this->user->update($userId, ['password_hash' => $newHash]);
    }
}
