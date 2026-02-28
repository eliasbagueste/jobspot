<?php
// config/database.php

require_once __DIR__ . '/config.php';

/**
 * Valores por defecto (local)
 * Se pueden sobrescribir en config/env.php
 */
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'jobspot');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

/**
 * Devuelve una conexión PDO (PHP Data Objects) a MySQL/MariaDB
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            if (APP_ENV === 'prod') {
                die('Error de conexión a la base de datos.');
            }

            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    return $pdo;
}