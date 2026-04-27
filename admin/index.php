<?php
// admin/index.php — Panel de administración principal

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

$user = $_SESSION['user'];
$pdo  = getPDO();

// =========================================================
// ESTADÍSTICAS RÁPIDAS PARA EL PANEL
// =========================================================

// Total de usuarios registrados en la plataforma
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Total de ofertas publicadas actualmente
$publishedJobs = (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'published'")->fetchColumn();

// Total de categorías activas disponibles
$activeCategories = (int) $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();

// Empresas pendientes de verificación
$pendingCompanies = (int) $pdo->query("SELECT COUNT(*) FROM companies WHERE is_verified = 0")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-welcome">
    <div>
        <h1>Panel de administración</h1>
        <p>Bienvenido, <?= htmlspecialchars($user['full_name']); ?>.</p>
    </div>
</div>

<!-- Resumen estadístico rápido -->
<div class="admin-grid">
    <div class="admin-stat">
        <div class="admin-stat-icon">👥</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $totalUsers; ?></div>
            <div class="admin-stat-label">Usuarios registrados</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon">📢</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $publishedJobs; ?></div>
            <div class="admin-stat-label">Ofertas publicadas</div>
        </div>
    </div>
    <div class="admin-stat" <?= $pendingCompanies > 0 ? 'style="border-left:3px solid #f59e0b;"' : ''; ?>>
        <div class="admin-stat-icon">🏢</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $pendingCompanies; ?></div>
            <div class="admin-stat-label">Empresas pendientes</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon">🏷</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $activeCategories; ?></div>
            <div class="admin-stat-label">Categorías activas</div>
        </div>
    </div>
</div>

<!-- Accesos directos a las secciones de administración -->
<div class="admin-grid">
    <a href="<?= BASE_URL; ?>/admin/users.php" class="admin-card">
        <div class="admin-card-icon">👥</div>
        <h2>Usuarios</h2>
        <p>Gestiona los usuarios registrados en la plataforma.</p>
    </a>

    <a href="<?= BASE_URL; ?>/admin/jobs.php" class="admin-card">
        <div class="admin-card-icon">💼</div>
        <h2>Ofertas</h2>
        <p>Consulta y gestiona todas las ofertas publicadas en la plataforma.</p>
    </a>

    <a href="<?= BASE_URL; ?>/admin/companies.php" class="admin-card"
       style="<?= $pendingCompanies > 0 ? 'border-left:4px solid #f59e0b;' : ''; ?>">
        <div class="admin-card-icon">🏢</div>
        <h2>
            Empresas
            <?php if ($pendingCompanies > 0): ?>
                <span class="badge badge-company" style="font-size:0.75rem; vertical-align:middle;">
                    <?= $pendingCompanies; ?> pendiente<?= $pendingCompanies !== 1 ? 's' : ''; ?>
                </span>
            <?php endif; ?>
        </h2>
        <p>Verifica las empresas registradas para que puedan publicar ofertas.</p>
    </a>

    <a href="<?= BASE_URL; ?>/admin/categories.php" class="admin-card">
        <div class="admin-card-icon">🏷</div>
        <h2>Categorías</h2>
        <p>Añade, activa o desactiva las categorías de ofertas.</p>
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>