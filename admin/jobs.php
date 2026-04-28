<?php
// admin/jobs.php — Gestión de ofertas de trabajo
// El admin puede ver todas las ofertas y cerrar las que considere problemáticas.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

$user = $_SESSION['user'];
$pdo  = getPDO();


// =========================================================
// FILTRO DE ESTADO
// =========================================================
$validFilters = ['all', 'published', 'closed'];
$filter = in_array($_GET['filter'] ?? '', $validFilters) ? $_GET['filter'] : 'all';

$where = $filter !== 'all' ? "WHERE j.status = " . $pdo->quote($filter) : '';

$stmtJobs = $pdo->query("
    SELECT
        j.id,
        j.title,
        j.location,
        j.contract_type,
        j.modality,
        j.status,
        j.created_at,
        j.published_at,
        c.brand_name  AS company_name,
        cat.name      AS category_name,
        COUNT(a.id)   AS total_applications
    FROM jobs j
    JOIN companies  c   ON c.id  = j.company_id
    JOIN categories cat ON cat.id = j.category_id
    LEFT JOIN applications a ON a.job_id = j.id
    $where
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$jobs = $stmtJobs->fetchAll();

// Contadores por estado para los filtros
$counts = $pdo->query("
    SELECT status, COUNT(*) AS n FROM jobs GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$totalJobs = array_sum($counts);

$statusLabels = [
    'draft'     => 'Borrador',
    'pending'   => 'Pendiente',
    'published' => 'Publicada',
    'closed'    => 'Cerrada',
    'rejected'  => 'Rechazada',
];

$statusClass = [
    'draft'     => 'badge-candidate',
    'pending'   => 'badge-company',
    'published' => 'badge-active',
    'closed'    => 'badge-admin',
    'rejected'  => 'badge-rejected',
];

$contractLabels = [
    'permanent'  => 'Indefinido',
    'temporary'  => 'Temporal',
    'internship' => 'Prácticas',
    'freelance'  => 'Freelance',
];

$modalityLabels = [
    'onsite' => 'Presencial',
    'hybrid' => 'Híbrido',
    'remote' => 'Remoto',
];

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/admin/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="card" style="padding: 16px 24px;">
    <h1 style="margin:0 0 0.25rem;">Ofertas</h1>
    <p style="color:#64748b; margin:0;">Consulta y gestiona todas las ofertas de la plataforma.</p>
</section>

<!-- Filtros por estado -->
<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
    <a href="?filter=all"
       class="<?= $filter === 'all' ? 'btn-primary' : 'btn-edit'; ?>">
        Todas (<?= $totalJobs; ?>)
    </a>
    <a href="?filter=published"
       class="<?= $filter === 'published' ? 'btn-primary' : 'btn-edit'; ?>">
        Publicadas (<?= $counts['published'] ?? 0; ?>)
    </a>
    <a href="?filter=closed"
       class="<?= $filter === 'closed' ? 'btn-primary' : 'btn-edit'; ?>">
        Cerradas (<?= $counts['closed'] ?? 0; ?>)
    </a>
</div>

<?php if (empty($jobs)): ?>
    <section class="card">
        <p>No hay ofertas en esta categoría.</p>
    </section>
<?php else: ?>
    <p style="margin-bottom:1rem; color:#64748b;">
        <?= count($jobs); ?> oferta<?= count($jobs) !== 1 ? 's' : ''; ?>
    </p>

    <?php foreach ($jobs as $job): ?>
        <div class="job-card">
            <div class="job-card-header">
                <div>
                    <h2 class="job-title">
                        <a href="<?= BASE_URL; ?>/admin/job-detail.php?id=<?= $job['id']; ?>" style="color:inherit; text-decoration:none;">
                            <?= htmlspecialchars($job['title']); ?>
                        </a>
                    </h2>
                    <p class="job-company"><?= htmlspecialchars($job['company_name']); ?></p>
                </div>
                <span class="badge <?= $statusClass[$job['status']] ?? 'badge-candidate'; ?>">
                    <?= $statusLabels[$job['status']] ?? $job['status']; ?>
                </span>
            </div>

            <div class="job-meta">
                <span>📁 <?= htmlspecialchars($job['category_name']); ?></span>
                <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>
                <span>📅 <?= date('d/m/Y', strtotime($job['created_at'])); ?></span>
            </div>

            <div style="border-top:1px solid #f1f5f9; margin-top:0.75rem; padding-top:0.75rem; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.85rem; color:#64748b;">
                    👥 <?= $job['total_applications']; ?> candidatura<?= $job['total_applications'] != 1 ? 's' : ''; ?> recibida<?= $job['total_applications'] != 1 ? 's' : ''; ?>
                </span>
                <a href="<?= BASE_URL; ?>/admin/job-detail.php?id=<?= $job['id']; ?>" class="btn-primary" style="font-size:0.85rem; padding:0.35rem 0.9rem;">Ver</a>
            </div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
