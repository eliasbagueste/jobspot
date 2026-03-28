<?php
// config/config.php

/**
 * Inicia la sesión si todavía no está iniciada.
 * Esto permite usar $_SESSION en cualquier parte de la aplicación
 * donde se cargue este archivo.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Nombre de la aplicación.
 * Se puede reutilizar en títulos, cabeceras o textos comunes.
 */
define('APP_NAME', 'JobSpot');

/**
 * La aplicación siempre cuelga de la raíz del host.
 *
 * Ejemplos válidos:
 * - http://jobspot.local
 * - https://dev.jobspot.es
 * - https://jobspot.es
 *
 * Por eso BASE_URL se mantiene como cadena vacía.
 * Así evitamos rutas hardcodeadas como /jobspot/login.php
 */
define('BASE_URL', '');

/**
 * Detecta el entorno actual en función del host.
 * Esto permite diferenciar entre:
 * - producción
 * - desarrollo
 * - local
 *
 * Se elimina el puerto si existiera, por ejemplo:
 * jobspot.local:8080 -> jobspot.local
 */
$host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
$host = explode(':', $host)[0];

/**
 * Define la constante APP_ENV según el dominio usado.
 */
if ($host === 'jobspot.es' || $host === 'www.jobspot.es') {
    define('APP_ENV', 'prod');
} elseif ($host === 'dev.jobspot.es') {
    define('APP_ENV', 'dev');
} else {
    // local: jobspot.local, localhost, 127.0.0.1, etc.
    define('APP_ENV', 'local');
}

/**
 * Ruta al archivo de configuración privada del entorno.
 *
 * Este archivo:
 * - NO se sube a GitHub
 * - SÍ debe existir en cada entorno real
 *
 * Aquí se guardan las credenciales de la base de datos
 * y cualquier otro secreto o configuración sensible.
 * __DIR__ es la variable mágica que apunta al directorio actual
 */
$envFile = __DIR__ . '/env.php';

/**
 * En JobSpot queremos que env.php sea obligatorio,
 * porque es la fuente única de configuración por entorno.
 *
 * Si no existe, detenemos la ejecución con un mensaje claro.
 * Así evitamos comportamientos ambiguos o configuraciones ocultas.
 */
if (!file_exists($envFile)) {
    die('Falta el archivo de configuración obligatorio: config/env.php');
}

/**
 * Carga la configuración privada del entorno.
 * Aquí se definirán, entre otras, las constantes:
 * - DB_HOST
 * - DB_NAME
 * - DB_USER
 * - DB_PASS
 */
require_once $envFile;