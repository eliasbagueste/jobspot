<?php
// admin/user-delete.php — Elimina un usuario de la base de datos
// Solo acepta peticiones POST para evitar borrados accidentales con un simple enlace.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

// Rechazamos GET: el borrado siempre debe venir de un formulario POST con confirmación
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id === 0) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$pdo  = getPDO();
$stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);

header('Location: ' . BASE_URL . '/admin/users.php');
exit;
