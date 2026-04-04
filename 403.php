<?php
require_once __DIR__ . '/config/config.php';
http_response_code(403);
require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align:center; padding: 50px;">
    <h1>403 - Acceso denegado</h1>
    <p>No tienes permiso para acceder a esta página.</p>
    <a href="/">Volver al inicio</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
