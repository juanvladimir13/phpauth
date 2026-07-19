-- Datos iniciales
INSERT INTO roles (name) VALUES ('admin'), ('cliente'), ('soporte');

INSERT INTO permissions (name) VALUES 
('view_dashboard'),
('manage_users'),
('manage_roles');

-- Permisos admin
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin';

-- Permisos soporte
INSERT INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'soporte' AND p.name = 'view_dashboard';
