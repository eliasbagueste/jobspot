<?php
// Carga la conexión a la base de datos y la configuración general
require_once __DIR__ . '/../config/database.php';

// Carga las funciones de autenticación
require_once __DIR__ . '/../includes/auth.php';

// Si no hay sesión → redirige al login
requireLogin();

// Si el usuario no es admin → error 403
requireRole('admin');

// Recogemos el id de la URL
$id = (int) ($_GET['id'] ?? 0);

// Si no hay id válido, volvemos a la lista
if ($id === 0) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Borramos el usuario
$pdo  = getPDO();
$stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);

// Redirigimos a la lista
header('Location: ' . BASE_URL . '/admin/users.php');
exit;