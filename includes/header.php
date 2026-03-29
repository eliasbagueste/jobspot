<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME; ?></title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="brand">
                <a href="<?= BASE_URL; ?>/index.php"><?= APP_NAME; ?></a>
            </div>
            <nav class="nav">
                <a href="<?= BASE_URL; ?>/index.php">Inicio</a>
                <a href="<?= BASE_URL; ?>/jobs.php">Ofertas</a>
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL; ?>/admin/index.php">Panel admin</a>
                    <?php elseif ($_SESSION['user']['role'] === 'candidate'): ?>
                        <a href="<?= BASE_URL; ?>/candidate/index.php">Mi perfil</a>
                    <?php elseif ($_SESSION['user']['role'] === 'company'): ?>
                        <a href="<?= BASE_URL; ?>/company/index.php">Mi empresa</a>
                        <a href="<?= BASE_URL; ?>/company/jobs.php">Mis ofertas</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL; ?>/logout.php">Cerrar sesión</a>
                <?php else: ?>
                    <a href="<?= BASE_URL; ?>/login.php">Login</a>
                    <a href="<?= BASE_URL; ?>/register.php">Registro</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">