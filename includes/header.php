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
                <a href="<?= BASE_URL; ?>/login.php">Login</a>
                <a href="<?= BASE_URL; ?>/register.php">Registro</a>
            </nav>
        </div>
    </header>

    <main class="container">