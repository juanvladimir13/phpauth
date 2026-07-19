<?php

namespace PhpAuth;

use PhpAuth\Controllers\Auth;
use PhpAuth\Controllers\Csrf;
use PhpAuth\Controllers\Guard;
use PhpAuth\Controllers\RateLimiter;
use PhpAuth\Rbac\AuthManager;

/**
 * AuthManager — Singleton que centraliza el acceso a todos los
 * componentes del sistema de autenticación y autorización.
 *
 * Uso:
 *   $manager = AuthManager::getInstance();
 *   $manager->auth()->login($username, $password);
 *   $manager->guard()->requireLogin();
 *   AuthManager::csrf()::generateToken();
 *   $manager->rateLimiter()->isLockedOut($ip);
 *   $manager->rbac()->seed();
 */
class AuthRbac
{
    /** @var self|null */
    private static ?self $instance = null;

    private ?Auth $auth = null;
    private ?Guard $guard = null;
    private ?RateLimiter $rateLimiter = null;
    private ?AuthManager $rbacSeeder = null;

    /**
     * Constructor privado — impide instanciación directa.
     */
    private function __construct()
    {
    }

    /**
     * Evita la clonación de la instancia singleton.
     */
    private function __clone()
    {
    }

    /**
     * Evita la deserialización de la instancia singleton.
     *
     * @throws \RuntimeException
     */
    public function __wakeup(): void
    {
        throw new \RuntimeException('No se puede deserializar un singleton.');
    }

    /**
     * Obtiene la instancia única de AuthManager.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Controlador de autenticación (login, logout, register).
     */
    public function auth(): Auth
    {
        if ($this->auth === null) {
            $this->auth = new Auth();
        }

        return $this->auth;
    }

    /**
     * Controlador de autorización / middleware (requireLogin, requireRole, requireCan).
     */
    public function guard(): Guard
    {
        if ($this->guard === null) {
            $this->guard = new Guard();
        }

        return $this->guard;
    }

    /**
     * Genera un token CSRF (atajo estático).
     */
    public static function csrfToken(): string
    {
        return Csrf::generateToken();
    }

    /**
     * Verifica un token CSRF (atajo estático).
     */
    public static function csrfVerify(?string $token): bool
    {
        return Csrf::verifyToken($token);
    }

    /**
     * Controlador de rate limiting para intentos de login.
     */
    public function rateLimiter(int $maxAttempts = 5, int $lockoutTime = 900): RateLimiter
    {
        if ($this->rateLimiter === null) {
            $this->rateLimiter = new RateLimiter($maxAttempts, $lockoutTime);
        }

        return $this->rateLimiter;
    }

    /**
     * Seeder RBAC (roles, permisos y usuarios desde YAML).
     */
    public function rbac(): AuthManager
    {
        if ($this->rbacSeeder === null) {
            $this->rbacSeeder = new AuthManager();
        }

        return $this->rbacSeeder;
    }

    /**
     * Reinicia la instancia singleton.
     * Útil para testing o reset completo del estado.
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
