<?php
// logout.php — Cierra la sesión del usuario y redirige al login

require_once __DIR__ . '/config/config.php';

// Vaciamos todos los datos de la sesión
$_SESSION = [];

// Eliminamos también la cookie de sesión del navegador si existe
// Esto es necesario para que el cierre de sesión sea completo
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    // Enviamos la cookie con fecha de expiración pasada para que el navegador la borre
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

// Destruimos la sesión en el servidor
session_destroy();

header('Location: ' . BASE_URL . '/login.php');
exit;