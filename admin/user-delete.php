<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

// Solo aceptamos POST para evitar borrados accidentales por URL
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
