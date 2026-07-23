<?php
// company/jobs.php — Listado de ofertas de la empresa

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Verificamos que la empresa tiene perfil creado antes de mostrar sus ofertas
$stmt = $pdo->prepare("SELECT * FROM companies WHERE owner_user_id = :uid");
$stmt->execute(['uid' => $user['id']]);
$company = $stmt->fetch();

// Si no tiene perfil, redirigimos al panel para que lo cree
if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// Cargamos todas las ofertas de esta empresa con su categoría y el número de candidaturas
// COUNT y SUM con CASE nos permiten calcular totales y pendientes en una sola consulta
$stmtJobs = $pdo->prepare("
    SELECT
        j.id,
        j.title,
        j.location,
        j.contract_type,
        j.modality,
        j.status,
        j.created_at,
        j.published_at,
        cat.name AS category_name,
        COUNT(a.id) AS total_applications,
        SUM(CASE WHEN a.status IN ('sent', 'reviewed') THEN 1 ELSE 0 END) AS pending_applications
    FROM jobs j
    JOIN categories cat ON cat.id = j.category_id
    LEFT JOIN applications a ON a.job_id = j.id
    WHERE j.company_id = :cid
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$stmtJobs->execute(['cid' => $company['id']]);
$jobs = $stmtJobs->fetchAll();

// Etiquetas y clases CSS para los estados de las ofertas
$statusLabels = [
    'draft'     => 'Draft',
    'pending'   => 'Pending',
    'published' => 'Published',
    'closed'    => 'Closed',
    'rejected'  => 'Rejected',
];

$statusClass = [
    'draft'     => 'badge-candidate',
    'pending'   => 'badge-company',
    'published' => 'badge-active',
    'closed'    => 'badge-admin',
    'rejected'  => 'badge-rejected',
];

$contractLabels = [
    'permanent'  => 'Permanent',
    'temporary'  => 'Temporary',
    'internship' => 'Internship',
    'freelance'  => 'Freelance',
];

$modalityLabels = [
    'onsite' => 'On-site',
    'hybrid' => 'Hybrid',
    'remote' => 'Remote',
];

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_GET['edited'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem;">
        Job updated successfully.
    </div>
<?php endif; ?>
<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem;">
        Job created and published successfully.
    </div>
<?php endif; ?>

<a href="<?= BASE_URL; ?>/company/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to dashboard
</a>

<section class="card">
    <h1 style="margin:0 0 0.4rem;">My jobs</h1>
    <p style="margin:0; color:#64748b; font-size:0.9rem;">Manage and review all the job listings you have published.</p>
</section>

<div style="margin:1.75rem 0;">
    <a href="<?= BASE_URL; ?>/company/job-create.php" class="btn-edit" style="padding:14px 32px; font-size:1rem;">+ Create new job listing</a>
</div>

<?php if (empty($jobs)): ?>
    <section class="card" style="margin-top:1rem;">
        <p>You have not published any jobs yet.</p>
        <a href="<?= BASE_URL; ?>/company/job-create.php" class="btn-primary" style="margin-top:1rem; display:inline-block;">
            Create first job listing
        </a>
    </section>

<?php else: ?>
    <p style="margin: 1rem 0; color:#64748b;">
        Total: <?= count($jobs); ?> job<?= count($jobs) !== 1 ? 's' : ''; ?>
    </p>

    <?php foreach ($jobs as $job): ?>
        <div class="job-card" style="display:flex; justify-content:space-between; gap:1.5rem; align-items:flex-start;">
            <!-- Columna izquierda: info -->
            <div style="flex:1;">
                <h2 class="job-title"><?= htmlspecialchars($job['title']); ?></h2>
                <p class="job-company"><?= htmlspecialchars($job['category_name']); ?></p>
                <div class="job-meta" style="margin-top:1.2rem;">
                    <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                    <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                    <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>
                    <span>👥 <?= $job['total_applications']; ?> application<?= $job['total_applications'] != 1 ? 's' : ''; ?><?php if ($job['pending_applications'] > 0 && $job['status'] === 'published'): ?> <span style="color:#ef4444; font-weight:600;">(<?= $job['pending_applications']; ?> pending decision)</span><?php endif; ?></span>
                </div>
            </div>

            <!-- Columna derecha: badge + botones -->
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:2.5rem; flex-shrink:0;">
                <span class="badge <?= $statusClass[$job['status']] ?? 'badge-candidate'; ?>">
                    <?= $statusLabels[$job['status']] ?? $job['status']; ?>
                </span>
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:flex-end;">
                    <?php if ($job['total_applications'] > 0): ?>
                        <a href="<?= BASE_URL; ?>/company/applications.php?job=<?= $job['id']; ?>" class="btn-edit">
                            View applications
                        </a>
                    <?php endif; ?>
                    <?php if (!in_array($job['status'], ['closed', 'rejected'])): ?>
                        <a href="<?= BASE_URL; ?>/company/job-edit.php?id=<?= $job['id']; ?>" class="btn-edit">
                            Edit
                        </a>
                    <?php endif; ?>
                    <?php if ($job['status'] === 'published'): ?>
                        <a href="<?= BASE_URL; ?>/company/job-delete.php?id=<?= $job['id']; ?>&action=close"
                           class="btn-delete"
                           onclick="return confirm('Close this job listing? Candidates will no longer be able to apply.');">
                            Close
                        </a>
                    <?php endif; ?>
                    <?php if ($job['status'] === 'closed'): ?>
                        <a href="<?= BASE_URL; ?>/company/job-delete.php?id=<?= $job['id']; ?>&action=reopen"
                           class="btn-edit"
                           onclick="return confirm('Reopen this job listing? It will become visible to candidates again.');">
                            Reopen
                        </a>
                    <?php endif; ?>
                    <?php if (in_array($job['status'], ['draft', 'rejected'])): ?>
                        <a href="<?= BASE_URL; ?>/company/job-delete.php?id=<?= $job['id']; ?>&action=delete"
                           class="btn-delete"
                           onclick="return confirm('Permanently delete this job listing?');">
                            Delete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
