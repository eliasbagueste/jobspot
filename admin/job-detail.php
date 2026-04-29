<?php
// admin/job-detail.php — Detalle de una oferta para el administrador

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

$pdo = getPDO();

// Recogemos el ID de la oferta desde la URL
$jobId = (int) ($_GET['id'] ?? 0);
if ($jobId === 0) {
    header('Location: ' . BASE_URL . '/admin/jobs.php');
    exit;
}

// Si el admin pulsa "Cerrar" o "Reabrir", procesamos la acción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'close') {
        // Cerramos la oferta y rechazamos automáticamente las candidaturas pendientes
        $pdo->prepare("UPDATE jobs SET status = 'closed' WHERE id = :id AND status = 'published'")->execute(['id' => $jobId]);
        $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE job_id = :jid AND status IN ('sent', 'reviewed')")->execute(['jid' => $jobId]);
    } elseif ($action === 'reopen') {
        // Reabrimos la oferta y actualizamos la fecha de publicación a ahora
        $pdo->prepare("UPDATE jobs SET status = 'published', published_at = NOW() WHERE id = :id AND status = 'closed'")->execute(['id' => $jobId]);
    }

    // Redirigimos a la misma página para evitar reenvío del formulario (POST-Redirect-GET)
    header('Location: ' . BASE_URL . '/admin/job-detail.php?id=' . $jobId);
    exit;
}

// Cargamos la oferta con empresa, categoría y número de candidaturas
// El admin puede ver cualquier oferta, independientemente de su estado
$stmt = $pdo->prepare("
    SELECT
        j.id,
        j.title,
        j.description,
        j.location,
        j.contract_type,
        j.workday,
        j.modality,
        j.salary_min,
        j.salary_max,
        j.currency,
        j.status,
        j.published_at,
        j.created_at,
        co.brand_name   AS company_name,
        co.logo_path    AS company_logo,
        co.location     AS company_location,
        co.description  AS company_description,
        cat.name        AS category_name,
        COUNT(a.id)     AS total_applications
    FROM jobs j
    JOIN companies  co  ON co.id  = j.company_id
    JOIN categories cat ON cat.id = j.category_id
    LEFT JOIN applications a ON a.job_id = j.id
    WHERE j.id = :id
    GROUP BY j.id
");
$stmt->execute(['id' => $jobId]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/admin/jobs.php');
    exit;
}

// Etiquetas legibles para los ENUM de la base de datos
$modalityLabels = [
    'onsite' => 'Presencial',
    'hybrid' => 'Híbrido',
    'remote' => 'Remoto',
];

$contractLabels = [
    'permanent'  => 'Indefinido',
    'temporary'  => 'Temporal',
    'internship' => 'Prácticas',
    'freelance'  => 'Freelance',
];

$workdayLabels = [
    'full_time' => 'Jornada completa',
    'part_time' => 'Media jornada',
];

// Etiquetas y clases CSS para el estado de la oferta
$statusLabels = [
    'published' => 'Publicada',
    'closed'    => 'Cerrada',
    'draft'     => 'Borrador',
    'rejected'  => 'Rechazada',
];

$statusClass = [
    'published' => 'badge-active',   // verde
    'closed'    => 'badge-admin',    // gris
    'draft'     => 'badge-candidate', // azul
    'rejected'  => 'badge-rejected', // rojo
];

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/admin/jobs.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver a ofertas
</a>

<?php if (isset($_GET['edited'])): ?>
    <div class="alert alert-success" style="margin-bottom:1rem;">Oferta actualizada correctamente.</div>
<?php endif; ?>

<div class="job-detail-layout">

    <!-- Columna principal -->
    <div class="job-detail-main">

        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                <div>
                    <span class="badge badge-candidate" style="margin-bottom:8px; display:inline-block;">
                        <?= htmlspecialchars($job['category_name']); ?>
                    </span>
                    <h1 style="margin:0 0 4px; font-size:1.5rem;">
                        <?= htmlspecialchars($job['title']); ?>
                    </h1>
                    <p style="margin:0; color:#64748b; font-size:0.95rem;">
                        <?= htmlspecialchars($job['company_name']); ?>
                    </p>
                </div>
                <span class="badge <?= $statusClass[$job['status']] ?? 'badge-candidate'; ?>">
                    <?= $statusLabels[$job['status']] ?? $job['status']; ?>
                </span>
            </div>

            <div class="job-meta" style="margin-top:16px; padding-top:16px; border-top:1px solid #f1f5f9;">
                <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                <span>🕐 <?= $workdayLabels[$job['workday']] ?? $job['workday']; ?></span>
                <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>
                <?php if ($job['salary_min'] !== null && $job['salary_max'] !== null): ?>
                    <span>
                        💶 <?= number_format((float)$job['salary_min'], 0, ',', '.'); ?>
                        – <?= number_format((float)$job['salary_max'], 0, ',', '.'); ?>
                        <?= htmlspecialchars($job['currency']); ?> brutos/año
                    </span>
                <?php endif; ?>
            </div>

            <div style="border-top:1px solid #f1f5f9; margin-top:12px; padding-top:12px; font-size:0.9rem; color:#64748b;">
                👥 <?= $job['total_applications']; ?> candidatura<?= $job['total_applications'] != 1 ? 's' : ''; ?> recibida<?= $job['total_applications'] != 1 ? 's' : ''; ?>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-top:0; font-size:1.05rem; color:#0b132b;">Descripción del puesto</h2>
            <div style="color:#374151; line-height:1.7; white-space:pre-line; font-size:0.95rem;">
                <?= htmlspecialchars($job['description']); ?>
            </div>
        </div>
    </div>

    <!-- Columna lateral: acciones admin + empresa -->
    <div class="job-detail-aside">

        <!-- Acciones del administrador -->
        <div class="card" style="margin-bottom:16px;">
            <h3 style="margin-top:0; font-size:0.95rem; color:#0b132b;">Acciones</h3>

            <a href="<?= BASE_URL; ?>/admin/job-edit.php?id=<?= $job['id']; ?>"
               class="btn-edit" style="display:block; text-align:center; margin-bottom:8px;">
                Editar oferta
            </a>

            <form method="post" action="<?= BASE_URL; ?>/admin/job-detail.php?id=<?= $job['id']; ?>"
                  style="display:flex; flex-direction:column; gap:8px;">

                <?php if ($job['status'] === 'published'): ?>
                    <button type="submit" name="action" value="close" class="btn-delete"
                            onclick="return confirm('¿Cerrar esta oferta? Las candidaturas pendientes se rechazarán automáticamente.');">
                        Cerrar oferta
                    </button>
                <?php endif; ?>

                <?php if ($job['status'] === 'closed'): ?>
                    <button type="submit" name="action" value="reopen" class="btn-edit"
                            onclick="return confirm('¿Reabrir esta oferta? Volverá a estar visible para los candidatos.');">
                        Reabrir oferta
                    </button>
                <?php endif; ?>

            </form>

        </div>

        <!-- Información sobre la empresa -->
        <?php if (!empty($job['company_description'])): ?>
            <div class="card">
                <h3 style="margin-top:0; font-size:0.95rem; color:#0b132b;">Sobre la empresa</h3>
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:8px;">
                    <div class="company-avatar">
                        <?php if (!empty($job['company_logo'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($job['company_logo']); ?>"
                                 alt="<?= htmlspecialchars($job['company_name']); ?>">
                        <?php else: ?>
                            <!-- mb_substr saca la primera letra del nombre; mb_strtoupper la pone en mayúscula -->
                            <!-- Las funciones mb_ trabajan bien con caracteres especiales (tildes, ñ...) -->
                            <?= mb_strtoupper(mb_substr($job['company_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <p style="font-weight:600; margin:0; font-size:0.95rem;">
                        <?= htmlspecialchars($job['company_name']); ?>
                    </p>
                </div>
                <?php if (!empty($job['company_location'])): ?>
                    <p style="margin:0 0 10px; color:#64748b; font-size:0.85rem;">
                        📍 <?= htmlspecialchars($job['company_location']); ?>
                    </p>
                <?php endif; ?>
                <p style="margin:0; color:#374151; font-size:0.88rem; line-height:1.6;">
                    <?= htmlspecialchars($job['company_description']); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
