<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Bienvenido a <?= APP_NAME; ?></h1>
    <p>Bolsa de empleo local para conectar candidatos y empresas.</p>
    <p>Proyecto DAW - Equipo formado por: Fátima, Sufian y Elías.</p>
</section>

<section class="grid">
    <div class="card">
        <h2>Para candidatos</h2>
        <p>Crea tu perfil, guarda ofertas y envía candidaturas.</p>
    </div>

    <div class="card">
        <h2>Para empresas</h2>
        <p>Publica ofertas, gestiona candidaturas y encuentra talento local.</p>
    </div>

    <div class="card">
        <h2>Panel admin</h2>
        <p>Modera ofertas, gestiona categorías y controla usuarios.</p>
    </div>

    <div class="card">
        <h2>Siguiente paso</h2>
        <p>Crear base de datos, login/registro y listado de ofertas.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>