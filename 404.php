<?php
require_once __DIR__ . '/config/config.php';
// Devuelve el código HTTP 404 al navegador (sin esto, devolvería 200 aunque sea un error)
http_response_code(404);
require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align:center; padding: 50px;">
    <h1>404 - Page not found</h1>
    <p>The page you are looking for does not exist.</p>
    <a href="/">Back to home</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>