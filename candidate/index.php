<?php
// Carga la configuración general
require_once __DIR__ . '/../config/config.php';

// Carga las funciones de autenticación
require_once __DIR__ . '/../includes/auth.php';

// Si no hay sesión → redirige al login
requireLogin();

// Si el usuario no es candidato → error 403
requireRole('candidate');

$user = $_SESSION['user'];

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <h1>Bienvenido, <?= htmlspecialchars($user['full_name']); ?></h1>
    <p>Estás en tu panel de candidato.</p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>