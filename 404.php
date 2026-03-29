<?php
require_once __DIR__ . '/config/config.php';
http_response_code(404);
require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align:center; padding: 50px;">
    <h1>404 - Página no encontrada</h1>
    <p>La página que buscas no existe.</p>
    <a href="<?= BASE_URL; ?>/">Volver al inicio</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>