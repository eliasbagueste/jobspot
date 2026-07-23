<?php
require_once __DIR__ . '/config/config.php';
http_response_code(403);
require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align:center; padding: 50px;">
    <h1>403 - Access denied</h1>
    <p>You do not have permission to access this page.</p>
    <a href="/">Back to home</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
