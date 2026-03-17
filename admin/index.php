<?php
// admin/index.php

/**
 * Carga la configuración general.
 * Aquí ya tendremos acceso a la sesión.
 */
require_once __DIR__ . '/../config/config.php';

/**
 * Protege la página:
 * si no hay usuario en sesión, redirige al login.
 */
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

/**
 * Comprueba que el usuario autenticado tenga rol admin.
 * Si no lo tiene, se devuelve error 403 (Forbidden, prohibido).
 */
if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

/**
 * Guardamos el usuario autenticado en una variable
 * para trabajar más cómodo en la vista.
 */
$user = $_SESSION['user'];

/**
 * Carga la cabecera común.
 */
require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <h1>Panel privado</h1>
    <p>Has iniciado sesión correctamente en JobSpot.</p>

    <div class="info-list">
        <p><strong>ID:</strong> <?= htmlspecialchars((string) $user['id']); ?></p>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($user['full_name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($user['role']); ?></p>
    </div>

    <p>
        <a href="<?= BASE_URL; ?>/logout.php" class="btn-link">Cerrar sesión</a>
    </p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>