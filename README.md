# PHP Authentication & Authorization System

Un sistema completo de control de acceso seguro para PHP vanilla (sin frameworks) con PostgreSQL.

## Funcionalidades
- **Autenticación**: Registro, Login, Logout seguro.
- **Autorización (RBAC + Permisos)**: Middleware reusable para proteger rutas por rol o permisos granulares.
- **Seguridad Integrada**:
  - `password_hash` con **ARGON2ID**.
  - Prevención de **SQL Injection** mediante `PDO` Prepared Statements y desactivación de emulación de consultas.
  - Prevención de **Session Fixation** (`session_regenerate_id`).
  - Mitigación de **XSS y CSRF** con sesiones `httponly` y `samesite=Lax`.
  - Protección activa contra **CSRF** mediante validación de tokens únicos con `hash_equals`.
  - Prevención de ataques de **fuerza bruta** (Rate Limiting).
  - Timeout por inactividad de sesión.

## Casos de Uso Soportados
- Paneles de administración o portales en HTML directo (Views tradicionales).
- Funcionalidades API que devuelven JSON si se detecta la cabecera `Accept: application/json` en los endpoints como `/login.php`, permitiendo la integración con un Frontend SPA / JS.

## Instalación

1. **Base de Datos**
   - Asegúrate de tener PostgreSQL ejecutándose.
   - Crea tu base de datos (por ejemplo, `auth_db`).
   - Ejecuta el script SQL para inicializar el esquema:
     ```bash
     psql -U postgres -d auth_db -f sql/schema.sql
     ```
   *(Nota: `schema.sql` ya incluye los roles `admin`, `cliente` y `soporte`, así como el permiso `view_dashboard`)*

2. **Variables de Entorno**
   - Copia `.env.example` a `.env`:
     ```bash
     cp .env.example .env
     ```
   - Edita el archivo `.env` configurando los datos de tu conexión PDO hacia PostgreSQL.

3. **Ejecución Local (Servidor de Desarrollo)**
   ```bash
   composer start
   # o bien:
   php -S localhost:8013 -t public/
   ```

## Pruebas de Uso
1. Entra a `http://localhost:8013/register.php` y regístrate. Por seguridad y buena práctica, el sistema asume que eres un `cliente` común sin el permiso `view_dashboard`.
2. Al ingresar e intentar navegar a `http://localhost:8013/dashboard.php`, el sistema **denegará** el acceso.
3. Puedes entrar a la base de datos y ascender a tu usuario cambiando el `role_id = 1` (admin) o `role_id = 3` (soporte).
4. Vuelve a iniciar sesión; ahora el Dashboard cargará sin problema.

## Uso del Guard
En cualquier archivo (ya sea vista o endpoint API), solo requieres `init.php` y hacer uso del `$guard`:
```php
require_once __DIR__ . '/init.php';

// Protege que el usuario esté logueado obligatoriamente
$guard->requireLogin(); 

// (Opcional) Protege por rol
$guard->requireRole('admin'); 

// (Opcional) Protege por permiso
$guard->requireCan('manage_users'); 
```
