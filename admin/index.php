<?php
// admin/index.php

/**
 * Carga la configuración general.
 * Aquí ya tendremos acceso a la sesión.
 */
// Carga la configuración general (sesión, constantes, etc.)
require_once __DIR__ . '/../config/config.php';

// Carga las funciones de autenticación (requireLogin y requireRole)
require_once __DIR__ . '/../includes/auth.php';

// Si no hay sesión → redirige al login
requireLogin();

// Si el usuario no es admin → error 403
requireRole('admin');

// Guardamos el usuario de sesión en una variable para usarlo en la vista
$user = $_SESSION['user'];

// Carga la cabecera HTML común
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-welcome">
    <div>
        <h1>Panel de administración</h1>
        <p>Bienvenido, <?= htmlspecialchars($user['full_name']); ?>.</p>
    </div>
</div>

<div class="admin-grid">
    <a href="<?= BASE_URL; ?>/admin/users.php" class="admin-card">
        <div class="admin-card-icon">👥</div>
        <h2>Usuarios</h2>
        <p>Gestiona los usuarios registrados en la plataforma.</p>
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>