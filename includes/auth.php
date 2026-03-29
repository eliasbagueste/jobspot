<?php

/**
 * Funciones de autenticación y control de acceso.
 * Se incluyen en las páginas protegidas para evitar repetir el mismo código.
 */

// Si no hay usuario en sesión → redirige al login y para la ejecución
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// Si el rol del usuario no coincide con el requerido → redirige a la página 403
function requireRole(string $role) {
    if ($_SESSION['user']['role'] !== $role) {
        header('Location: ' . BASE_URL . '/403.php');
        exit;
    }
}
