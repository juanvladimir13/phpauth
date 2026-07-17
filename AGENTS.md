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

## Notes

- No test framework installed yet (phpunit not in require-dev)
- No CI workflows present
- `src/` and `tests/` directories are empty — this is a fresh skeleton
