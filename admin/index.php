<?php
// admin/index.php — Panel de administración principal

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Estadísticas rápidas para el panel de administración
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
        <h1>Admin dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($user['full_name']); ?>.</p>
    </div>
</div>

<!-- Resumen estadístico rápido -->
<div class="admin-grid">
    <div class="admin-stat">
        <div class="admin-stat-icon">👤</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $totalUsers; ?></div>
            <div class="admin-stat-label">Registered users</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon">📢</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $publishedJobs; ?></div>
            <div class="admin-stat-label">Published jobs</div>
        </div>
    </div>
    <div class="admin-stat" <?= $pendingCompanies > 0 ? 'style="border-left:3px solid #f59e0b;"' : ''; ?>>
        <div class="admin-stat-icon">🏬</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $pendingCompanies; ?></div>
            <div class="admin-stat-label">Companies pending verification</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon">🏷</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $activeCategories; ?></div>
            <div class="admin-stat-label">Active categories</div>
        </div>
    </div>
</div>

<!-- Accesos directos a las secciones de administración -->
<div class="admin-grid">
    <a href="<?= BASE_URL; ?>/admin/users.php" class="admin-card">
        <div class="admin-card-icon">👥</div>
        <h2>Users</h2>
        <p>Manage the users registered on the platform.</p>
    </a>

    <a href="<?= BASE_URL; ?>/admin/jobs.php" class="admin-card">
        <div class="admin-card-icon">💼</div>
        <h2>Jobs</h2>
        <p>View and manage all job listings published on the platform.</p>
    </a>

    <a href="<?= BASE_URL; ?>/admin/companies.php" class="admin-card"
       style="<?= $pendingCompanies > 0 ? 'border-left:4px solid #f59e0b;' : ''; ?>">
        <div class="admin-card-icon">🏢</div>
        <h2 style="display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap;">
            Companies
            <?php if ($pendingCompanies > 0): ?>
                <span class="badge badge-company" style="font-size:0.75rem;">
                    <?= $pendingCompanies; ?> to verify
                </span>
            <?php endif; ?>
        </h2>
        <p>Verify registered companies so they can publish job listings.</p>
    </a>

    <a href="<?= BASE_URL; ?>/admin/categories.php" class="admin-card">
        <div class="admin-card-icon">🏷</div>
        <h2>Categories</h2>
        <p>Add, activate or deactivate job categories.</p>
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>