<?php
// config/database.php

/**
 * Carga la configuración general de la aplicación.
 *
 * Este archivo se encarga de:
 * - iniciar sesión si hace falta
 * - detectar el entorno
 * - cargar obligatoriamente config/env.php
 */
require_once __DIR__ . '/config.php';

/**
 * Devuelve una conexión PDO (PHP Data Objects, capa de acceso a base de datos en PHP).
 *
 * Se usa una variable estática para reutilizar la misma conexión
 * durante la ejecución actual del script y no crear varias conexiones innecesarias.
 *
 * @return PDO
 */
function getPDO(): PDO
{
    static $pdo = null;

    /**
     * Solo crea la conexión la primera vez.
     * En llamadas posteriores devuelve la ya creada.
     */
    if ($pdo === null) {
        /**
         * DSN (Data Source Name, cadena de conexión).
         *
         * Usa las constantes definidas en config/env.php:
         * - DB_HOST
         * - DB_NAME
         *
         * Se establece utf8mb4 para soportar correctamente
         * caracteres especiales, tildes, emojis, etc.
         */
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            /**
             * Crea la conexión PDO con las credenciales del entorno.
             *
             * Opciones usadas:
             * - ERRMODE_EXCEPTION:
             *   lanza excepciones cuando hay errores SQL o de conexión.
             *
             * - DEFAULT_FETCH_MODE => FETCH_ASSOC:
             *   devuelve los resultados como arrays asociativos.
             *
             * - EMULATE_PREPARES => false:
             *   fuerza el uso de consultas preparadas reales siempre que sea posible.
             */
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            /**
             * En producción no conviene mostrar detalles técnicos del error,
             * para no exponer información sensible.
             *
             * En local y desarrollo sí mostramos el mensaje completo
             * para facilitar el diagnóstico.
             */
            if (APP_ENV === 'prod') {
                die('Error de conexión a la base de datos.');
            }

            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    return $pdo;
}