<?php
// config/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nombre app
define('APP_NAME', 'JobSpot');

/**
 * La app siempre cuelga de la raíz del host:
 * - http://jobspot.local
 * - https://dev.jobspot.es
 * - https://jobspot.es
 */
define('BASE_URL', '');

/**
 * Detecta entorno por host
 */
$host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
$host = explode(':', $host)[0]; // por si hay puerto

if ($host === 'jobspot.es' || $host === 'www.jobspot.es') {
    define('APP_ENV', 'prod');
} elseif ($host === 'dev.jobspot.es') {
    define('APP_ENV', 'dev');
} else {
    // local: jobspot.local, localhost, 127.0.0.1, etc.
    define('APP_ENV', 'local');
}

/**
 * Carga overrides locales (no versionados) si existen:
 * - config/env.php (cada miembro lo crea en su PC/QNAP)
 * - Aquí guardamos credenciales BD y secretos
 */
$envFile = __DIR__ . '/env.php';
if (file_exists($envFile)) {
    require_once $envFile;
}