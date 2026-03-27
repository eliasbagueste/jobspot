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
-- Password: Admin1234
--
-- Se borra antes por si ya existia en una importacion previa.
-- =========================================================

DELETE FROM users
WHERE email = 'admin@jobspot.local';

INSERT INTO users (
    full_name,
    email,
    password_hash,
    role,
    is_active
) VALUES (
    'Administrador JobSpot',
    'admin@jobspot.local',
    '$2y$12$sPntFrOzJGWGyX81VQMIiOl8P.BHsKnC4QSlK2ALW6cog6.rm6smm',
    'admin',
    1
);