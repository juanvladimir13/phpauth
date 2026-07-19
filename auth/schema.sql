CREATE SCHEMA phpauth;

CREATE TABLE phpauth.roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE phpauth.permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE phpauth.role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER REFERENCES phpauth.roles(id) ON DELETE CASCADE,
    permission_id INTEGER REFERENCES phpauth.permissions(id) ON DELETE CASCADE,
    UNIQUE (role_id, permission_id)
);

CREATE TABLE phpauth.users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    celular      VARCHAR(15),
    activo        BOOLEAN      NOT NULL DEFAULT TRUE,
    role_id INTEGER REFERENCES phpauth.roles(id) ON DELETE SET NULL,
    phone_verified BOOLEAN     NOT NULL DEFAULT FALSE,
    editable BOOLEAN     NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_access_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE phpauth.login_attempts (
    id SERIAL PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    successful BOOLEAN DEFAULT FALSE
);
