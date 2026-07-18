# juanvladimir13-auth

## Analysis tools

- phpstan: `composer phpstan` — level 6, PHP 7.4 platform, scans `src` + `tests`
- psalm: `composer psalm` — level 8, PHP 7.4, scans `src` only
- phpcs: `composer phpcs` (summary) / `composer phpcs-detail` (full) — PSR12, 4-space indent
- php-cs-fixer: `composer phpcs-fixer` — PSR12 auto-fix on `src`

## Dev server

`composer start` — serves `./public` on port 8013 via PHP 7.4 built-in server.

## Conventions

- Namespace `App\` maps to `src/`, `Tests\` maps to `tests/`
- EditorConfig: 4-space indent, LF, 120-char line limit (2-space for JS/HTML)
- `composer.lock` is gitignored (lock intentionally excluded)
- Platform targets PHP 7.4 (`composer.json config.platform.php`)

## Project structure

- `src/` — 8 files:
  - `Auth.php` — Login, logout, register with `password_hash` (ARGON2ID)
  - `Csrf.php` — CSRF token generation (`random_bytes`) and verification (`hash_equals`)
  - `Guard.php` — Middleware: `requireLogin`, `requireRole`, `requireCan` (RBAC)
  - `RateLimiter.php` — Rate limiting by IP (5 attempts / 15 min lockout)
  - `Models/User.php`, `Role.php`, `Permission.php`, `LoginAttempt.php`
- `config/` — `database.php` (PG connection check), `session.php` (httponly, samesite=Lax, timeout, regeneration)
- `public/` — 5 entry points: `init.php` (bootstrap), `login.php`, `register.php`, `logout.php`, `dashboard.php`
- `sql/` — `schema.sql` (5 tables: roles, permissions, role_permissions, users, login_attempts) + `seed.sql` (admin/cliente/soporte roles, view_dashboard/manage_users/manage_roles permissions)
- `tests/` — Empty (no phpunit installed)

## Notes

- No test framework installed yet (phpunit not in require-dev)
- No CI workflows present
- External dependency: `juanvladimir13/postgres-database ^0.1.7`
- Static analysis: phpstan level 6 (src + tests), psalm level 8 (src only)
