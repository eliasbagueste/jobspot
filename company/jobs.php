<?php
// company/jobs.php — Listado de ofertas de la empresa

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// =========================================================
// VERIFICAMOS QUE LA EMPRESA TIENE PERFIL CREADO
// =========================================================
$stmt = $pdo->prepare("SELECT * FROM companies WHERE owner_user_id = :uid");
$stmt->execute(['uid' => $user['id']]);
$company = $stmt->fetch();

// Si no tiene perfil, redirigimos al panel para que lo cree
if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// =========================================================
// OBTENEMOS TODAS LAS OFERTAS DE ESTA EMPRESA
// =========================================================
// Hacemos JOIN con categories para mostrar el nombre de categoría.
// Incluimos también el conteo de candidaturas por oferta.
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

<?php if (isset($_GET['edited'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem;">
        Oferta actualizada correctamente.
    </div>
<?php endif; ?>
<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem;">
        Oferta creada y publicada correctamente.
    </div>
<?php endif; ?>

<a href="<?= BASE_URL; ?>/company/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="card">
    <h1 style="margin:0 0 0.4rem;">Mis ofertas</h1>
    <p style="margin:0; color:#64748b; font-size:0.9rem;">Gestiona y revisa todas las ofertas de empleo que has publicado.</p>
</section>

<div style="margin:1.75rem 0;">
    <a href="<?= BASE_URL; ?>/company/job-create.php" class="btn-edit" style="padding:14px 32px; font-size:1rem;">+ Crear nueva oferta</a>
</div>

<?php if (empty($jobs)): ?>
    <section class="card" style="margin-top:1rem;">
        <p>Aún no has publicado ninguna oferta.</p>
        <a href="<?= BASE_URL; ?>/company/job-create.php" class="btn-primary" style="margin-top:1rem; display:inline-block;">
            Crear primera oferta
        </a>
    </section>

<?php else: ?>
    <p style="margin: 1rem 0; color:#64748b;">
        Total: <?= count($jobs); ?> oferta<?= count($jobs) !== 1 ? 's' : ''; ?>
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
                    <span>👥 <?= $job['total_applications']; ?> candidatura<?= $job['total_applications'] != 1 ? 's' : ''; ?><?php if ($job['pending_applications'] > 0 && $job['status'] === 'published'): ?> <span style="color:#ef4444; font-weight:600;">(<?= $job['pending_applications']; ?> pendiente<?= $job['pending_applications'] != 1 ? 's' : ''; ?> de decisión)</span><?php endif; ?></span>
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
                            Ver candidaturas
                        </a>
                    <?php endif; ?>
                    <?php if (!in_array($job['status'], ['closed', 'rejected'])): ?>
                        <a href="<?= BASE_URL; ?>/company/job-edit.php?id=<?= $job['id']; ?>" class="btn-edit">
                            Editar
                        </a>
                    <?php endif; ?>
                    <?php if ($job['status'] === 'published'): ?>
                        <a href="<?= BASE_URL; ?>/company/job-delete.php?id=<?= $job['id']; ?>&action=close"
                           class="btn-delete"
                           onclick="return confirm('¿Cerrar esta oferta? Los candidatos ya no podrán aplicar.');">
                            Cerrar
                        </a>
                    <?php endif; ?>
                    <?php if ($job['status'] === 'closed'): ?>
                        <a href="<?= BASE_URL; ?>/company/job-delete.php?id=<?= $job['id']; ?>&action=reopen"
                           class="btn-edit"
                           onclick="return confirm('¿Reabrir esta oferta? Volverá a estar visible para los candidatos.');">
                            Reabrir
                        </a>
                    <?php endif; ?>
                    <?php if (in_array($job['status'], ['draft', 'rejected'])): ?>
                        <a href="<?= BASE_URL; ?>/company/job-delete.php?id=<?= $job['id']; ?>&action=delete"
                           class="btn-delete"
                           onclick="return confirm('¿Eliminar esta oferta definitivamente?');">
                            Eliminar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
