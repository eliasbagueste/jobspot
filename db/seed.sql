-- db/seed.sql

-- =========================================================
-- DATOS INICIALES DE PRUEBA PARA JOBSPOT
-- =========================================================
-- Este archivo inserta los datos minimos necesarios para
-- poder arrancar el proyecto y probar la autenticacion.
--
-- Uso:
-- 1. importar primero db/schema.sql
-- 2. importar despues db/seed.sql
--
-- Importante:
-- - Este archivo si se puede subir a GitHub
-- - Solo debe contener datos de prueba o iniciales
-- - No debe contener datos reales de produccion
--
-- =========================================================
-- USUARIO ADMINISTRADOR INICIAL
-- =========================================================
-- Credenciales de prueba:
-- Email: admin@jobspot.local
-- Password: GalwayDAW
--
-- Se borra antes por si ya existia en una importacion previa.
-- =========================================================

DELETE FROM users
WHERE email IN ('admin@jobspot.local', 'candidato@jobspot.local', 'company@jobspot.local');

INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES
(
    'Administrador',
    'admin@jobspot.local',
    '$2y$10$Mblltfy.5ml6.UuQ8/pG.OC/QRvxa1xhIjk9f4W5CBW.bloEw88PG',
    'admin',
    1
),
(
    'Company',
    'company@jobspot.local',
    '$2y$10$Mblltfy.5ml6.UuQ8/pG.OC/QRvxa1xhIjk9f4W5CBW.bloEw88PG',
    'company',
    1
),
(
    'Candidato',
    'candidato@jobspot.local',
    '$2y$10$Mblltfy.5ml6.UuQ8/pG.OC/QRvxa1xhIjk9f4W5CBW.bloEw88PG',
    'candidate',
    1
);
