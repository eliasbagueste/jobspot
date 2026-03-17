<?php
// logout.php

/**
 * Carga la configuración general.
 * Esto inicia sesión si todavía no estuviera iniciada.
 */
require_once __DIR__ . '/config/config.php';

/**
 * Vacía todas las variables de sesión.
 */
$_SESSION = [];

/**
 * Si la sesión usa cookies, elimina también la cookie de sesión.
 * Esto ayuda a cerrar la sesión de forma más limpia.
 */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/**
 * Destruye la sesión actual.
 */
session_destroy();

/**
 * Redirige al login.
 */
header('Location: ' . BASE_URL . '/login.php');
exit;