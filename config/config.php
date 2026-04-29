<?php
// config/config.php — Configuración general de la aplicación

// Iniciamos la sesión aquí para que esté disponible en todos los archivos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'JobSpot');

// BASE_URL está vacío porque la app siempre está en la raíz del dominio
// (jobspot.local, dev.jobspot.es o jobspot.es), nunca en subcarpeta
define('BASE_URL', '');

// Detectamos el entorno según el dominio para poder usar configuraciones distintas
$host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
$host = explode(':', $host)[0]; // quitamos el puerto si lo hay

if ($host === 'jobspot.es' || $host === 'www.jobspot.es') {
    define('APP_ENV', 'prod');
} elseif ($host === 'dev.jobspot.es') {
    define('APP_ENV', 'dev');
} elseif ($host === 'localhost' || $host === '127.0.0.1') {
    define('APP_ENV', 'local');
} else {
    // Cualquier otro host (servidor QNAP, NAS, etc.) se trata como dev
    define('APP_ENV', 'dev');
}

// En local mostramos errores para depurar; en cualquier otro entorno los ocultamos
if (APP_ENV === 'local') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// env.php contiene las credenciales de la BD y no se sube a GitHub
$envFile = __DIR__ . '/env.php';

if (!file_exists($envFile)) {
    die('Falta el archivo de configuración: config/env.php');
}

require_once $envFile;
