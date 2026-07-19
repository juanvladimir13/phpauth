# juanvladimir13-phpauth

## Analysis & test tools

- phpstan: `composer phpstan` — level 6, PHP 7.4 platform, scans `src` + `tests`
- psalm: `composer psalm` — level 8, PHP 7.4, scans `src` only
- phpcs: `composer phpcs` (summary) / `composer phpcs-detail` (full) — PSR12, 4-space indent
- php-cs-fixer: `composer phpcs-fixer` — PSR12 auto-fix on `src`
- phpunit: `composer test` — runs 74 unit tests

## Dev server

`composer start` — serves `./public` on port 8013 via PHP 7.4 built-in server.

## Conventions

- Namespace `PhpAuth\` maps to `src/`, `Tests\` maps to `tests/`
- EditorConfig: 4-space indent, LF, 120-char line limit (2-space for JS/HTML)
- `composer.lock` is gitignored (lock intentionally excluded)
- Platform targets PHP 7.4 (`composer.json config.platform.php`)

## Project structure

- `src/` — 10 files:
  - `AuthRbac.php` — Singleton facade: `auth()`, `guard()`, `rateLimiter()`, `rbac()`, `csrfToken()`/`csrfVerify()`
  - `Controllers/Auth.php` — Login, logout, register with `password_hash` (ARGON2ID)
  - `Controllers/Csrf.php` — CSRF token generation (`random_bytes`) and verification (`hash_equals`)
  - `Controllers/Guard.php` — Middleware: `requireLogin`, `requireRole`, `requireCan` (RBAC)
  - `Controllers/RateLimiter.php` — Rate limiting by IP (5 attempts / 15 min lockout)
  - `Models/User.php`, `Role.php`, `Permission.php`, `LoginAttempt.php`
  - `Rbac/AuthManager.php` — YAML-based RBAC seeder (roles, permissions, users)
- `auth/` — `session.php` (httponly, samesite=Lax, timeout, regeneration), `schema.sql`, `seed.sql`, `rbac.yml`
- `public/` — 6 entry points: `login.php`, `register.php`, `logout.php`, `dashboard.php`, `rbac_demo.php`, `rbac_yaml_demo.php`
- `tests/` — 74 unit tests (phpunit ^9.5):
  - `AuthRbacTest.php` — singleton, lazy loading, delegation
  - `Controllers/AuthTest.php`, `CsrfTest.php`, `GuardTest.php`, `RateLimiterTest.php`
  - `Models/UserTest.php`, `RoleTest.php`, `PermissionTest.php`, `LoginAttemptTest.php`
  - `Rbac/AuthManagerTest.php`

## Notes

- Test framework: phpunit ^9.5 installed, config at `phpunit.xml.dist`
- Test bootstrap: `tests/bootstrap.php` — suppresses pg_connect/header/session warnings in CLI
- No CI workflows present
- External dependency: `juanvladimir13/postgres-database ^0.1.7`
- Static analysis: phpstan level 6 (src + tests), psalm level 8 (src only)
