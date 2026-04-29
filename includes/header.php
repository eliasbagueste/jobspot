<?php
// includes/header.php — Cabecera HTML común a todas las páginas
// Se incluye al principio de cada página con require_once

// Cargamos la configuración general (sesión, BASE_URL, APP_NAME, etc.)
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Hace que la página se vea bien en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME; ?></title>
    <!-- Favicon: icono que aparece en la pestaña del navegador -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL; ?>/assets/img/favicon.svg">
    <!-- Hoja de estilos propia del proyecto -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
    <!-- Font Awesome: librería de iconos que usamos para el corazón, las redes sociales, etc. -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <!-- Logo / nombre de la aplicación que lleva a la portada -->
            <div class="brand">
                <a href="<?= BASE_URL; ?>/index.php"><?= APP_NAME; ?></a>
            </div>

            <!-- Botón hamburguesa: solo visible en móvil (CSS lo muestra/oculta) -->
            <!-- Las tres líneas se animan a una X cuando el menú está abierto -->
            <button class="nav-toggle" id="navToggle" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>

            <!-- Menú de navegación principal -->
            <!-- Los enlaces que se muestran dependen del rol del usuario o de si tiene sesión -->
            <nav class="nav" id="mainNav">
                <a href="<?= BASE_URL; ?>/index.php">Inicio</a>
                <a href="<?= BASE_URL; ?>/jobs.php">Ofertas</a>

                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <!-- El admin ve solo el enlace a su panel -->
                        <a href="<?= BASE_URL; ?>/admin/index.php">Panel admin</a>

                    <?php elseif ($_SESSION['user']['role'] === 'candidate'): ?>
                        <!-- El candidato ve su panel, candidaturas y favoritos -->
                        <a href="<?= BASE_URL; ?>/candidate/index.php">Panel</a>
                        <a href="<?= BASE_URL; ?>/candidate/my-applications.php">Mis candidaturas</a>
                        <a href="<?= BASE_URL; ?>/candidate/favorites.php">Favoritos</a>

                    <?php elseif ($_SESSION['user']['role'] === 'company'): ?>
                        <!-- La empresa ve su panel y sus ofertas -->
                        <a href="<?= BASE_URL; ?>/company/index.php">Mi empresa</a>
                        <a href="<?= BASE_URL; ?>/company/jobs.php">Mis ofertas</a>
                    <?php endif; ?>

                    <a href="<?= BASE_URL; ?>/logout.php" class="nav-btn-logout">Cerrar sesión</a>

                <?php else: ?>
                    <!-- Si no hay sesión activa mostramos login y registro -->
                    <a href="<?= BASE_URL; ?>/login.php">Iniciar sesión</a>
                    <a href="<?= BASE_URL; ?>/register.php" class="nav-btn-register">Registro</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Contenedor principal: aquí empieza el contenido de cada página -->
    <main class="container">
