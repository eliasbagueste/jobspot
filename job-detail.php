<?php
// job.php — Detalle de una oferta de trabajo
// Accesible para todos (visitantes, candidatos, empresas).
// Solo se muestran ofertas con estado 'published'.

require_once __DIR__ . '/config/database.php';

$pdo = getPDO();

// Recogemos el ID de la oferta desde la URL (?id=X)
$jobId = (int) ($_GET['id'] ?? 0);

if ($jobId === 0) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit;
}

// Cargamos la oferta con los datos de empresa y categoría
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
        j.published_at,
        co.brand_name   AS company_name,
        co.logo_path    AS company_logo,
        co.location     AS company_location,
        co.description  AS company_description,
        cat.name        AS category_name
    FROM jobs j
    JOIN companies  co  ON co.id  = j.company_id
    JOIN categories cat ON cat.id = j.category_id
    WHERE j.id = :id AND j.status = 'published'
");
$stmt->execute(['id' => $jobId]);
$job = $stmt->fetch();

// Si la oferta no existe o no está publicada, redirigimos al listado
if (!$job) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit;
}

// Comprobamos si el candidato ya ha aplicado y si tiene esta oferta en favoritos
$alreadyApplied = false;
$isFavorite     = false;
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'candidate') {
    $stmtCheck = $pdo->prepare("
        SELECT id FROM applications
        WHERE job_id = :jid AND candidate_user_id = :uid
    ");
    $stmtCheck->execute(['jid' => $jobId, 'uid' => $_SESSION['user']['id']]);
    $alreadyApplied = (bool) $stmtCheck->fetch();

    $stmtFav = $pdo->prepare("SELECT id FROM favorite_jobs WHERE candidate_user_id = :uid AND job_id = :jid");
    $stmtFav->execute(['uid' => $_SESSION['user']['id'], 'jid' => $jobId]);
    $isFavorite = (bool) $stmtFav->fetch();
}

$modalityLabels = [
    'onsite' => 'On-site',
    'hybrid' => 'Hybrid',
    'remote' => 'Remote',
];

$contractLabels = [
    'permanent'  => 'Permanent',
    'temporary'  => 'Temporary',
    'internship' => 'Internship',
    'freelance'  => 'Freelance',
];

$workdayLabels = [
    'full_time' => 'Full-time',
    'part_time' => 'Part-time',
];

require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 1rem;">
    <a href="<?= BASE_URL; ?>/jobs.php" class="btn-link">← Back to jobs</a>
</div>

<div class="job-detail-layout">

    <!-- ── Columna principal ───────────────────────────── -->
    <div class="job-detail-main">

        <!-- Cabecera de la oferta -->
        <div class="card" style="margin-bottom: 16px;">
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
                <p style="margin:0; color:#94a3b8; font-size:0.82rem; white-space:nowrap;">
                    Published on <?= date('d/m/Y', strtotime($job['published_at'])); ?>
                </p>
            </div>

            <!-- Etiquetas de condiciones -->
            <div class="job-meta" style="margin-top:16px; padding-top:16px; border-top:1px solid #f1f5f9;">
                <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                <span>🕐 <?= $workdayLabels[$job['workday']] ?? $job['workday']; ?></span>
                <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>
                <?php if ($job['salary_min'] !== null && $job['salary_max'] !== null): ?>
                    <span>
                        💶 <?= number_format((float)$job['salary_min'], 0, ',', '.'); ?>
                        – <?= number_format((float)$job['salary_max'], 0, ',', '.'); ?>
                        <?= htmlspecialchars($job['currency']); ?> gross/year
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Descripción completa de la oferta -->
        <div class="card">
            <h2 style="margin-top:0; font-size:1.05rem; color:#0b132b;">Job description</h2>
            <div style="color:#374151; line-height:1.7; white-space:pre-line; font-size:0.95rem;">
                <?= htmlspecialchars($job['description']); ?>
            </div>
        </div>
    </div>

    <!-- ── Columna lateral: acción + empresa ──────────── -->
    <div class="job-detail-aside">

        <!-- Botón de candidatura -->
        <div class="card" style="margin-bottom:16px; text-align:center;">
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'candidate'): ?>
                <?php if ($alreadyApplied): ?>
                    <p style="color:#166534; font-weight:600; margin:0 0 6px;">✓ You have already applied</p>
                    <p style="margin:0; color:#64748b; font-size:0.875rem;">
                        View in <a href="<?= BASE_URL; ?>/candidate/my-applications.php" class="btn-link">My applications</a>
                    </p>
                <?php else: ?>
                    <a href="<?= BASE_URL; ?>/candidate/apply.php?job=<?= $job['id']; ?>"
                       class="btn-primary" style="width:100%; display:block; text-align:center; padding:12px;">
                        Apply to this job
                    </a>
                <?php endif; ?>

                <button class="btn-favorite" data-job-id="<?= $job['id']; ?>"
                        data-favorited="<?= $isFavorite ? '1' : '0'; ?>"
                        style="background:none; border:none; cursor:pointer; font-size:0.9rem; color:<?= $isFavorite ? '#ef4444' : '#94a3b8'; ?>; margin-top:20px;">
                    <i class="<?= $isFavorite ? 'fas' : 'far'; ?> fa-heart"></i>
                    <span><?= $isFavorite ? 'Saved to favourites' : 'Save to favourites'; ?></span>
                </button>
            <?php elseif (isset($_SESSION['user'])): ?>
                <p style="color:#64748b; font-size:0.9rem; margin:0;">
                    Only candidates can apply to jobs.
                </p>
            <?php else: ?>
                <p style="margin:0 0 12px; color:#374151; font-size:0.9rem;">
                    Create a candidate account to apply.
                </p>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-primary"
                   style="width:100%; display:block; text-align:center; padding:12px;">
                    Sign up and apply
                </a>
                <a href="<?= BASE_URL; ?>/login.php" class="btn-link"
                   style="display:block; margin-top:10px; font-size:0.88rem;">
                    Already have an account? Log in
                </a>
            <?php endif; ?>
        </div>

        <!-- Información sobre la empresa -->
        <?php if (!empty($job['company_description'])): ?>
            <div class="card">
                <h3 style="margin-top:0; font-size:0.95rem; color:#0b132b;">About the company</h3>
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:8px;">
                    <div class="company-avatar">
                        <?php if (!empty($job['company_logo'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($job['company_logo']); ?>"
                                 alt="<?= htmlspecialchars($job['company_name']); ?>">
                        <?php else: ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
