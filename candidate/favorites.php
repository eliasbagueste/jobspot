<?php
// candidate/favorites.php — Ofertas guardadas como favoritas

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('candidate');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Cargo los favoritos del candidato con todos los datos de la oferta.
// Necesito los JOIN para obtener el nombre de la empresa y la categoría,
// que están en tablas separadas.
$stmt = $pdo->prepare("
    SELECT
        j.id,
        j.title,
        j.location,
        j.contract_type,
        j.workday,
        j.modality,
        j.salary_min,
        j.salary_max,
        j.currency,
        j.status,
        j.published_at,
        co.brand_name AS company_name,
        co.logo_path  AS company_logo,
        cat.name      AS category_name,
        fj.created_at AS saved_at
    FROM favorite_jobs fj
    JOIN jobs       j   ON j.id   = fj.job_id
    JOIN companies  co  ON co.id  = j.company_id
    JOIN categories cat ON cat.id = j.category_id
    WHERE fj.candidate_user_id = :uid
    ORDER BY fj.created_at DESC
");
$stmt->execute(['uid' => $user['id']]);
$favorites = $stmt->fetchAll();

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

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/candidate/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="card" style="padding:16px 24px;">
    <h1 style="margin:0 0 0.25rem;">Mis favoritos</h1>
    <p style="color:#64748b; margin:0;">Ofertas que has guardado para revisar más tarde.</p>
</section>

<?php if (empty($favorites)): ?>
    <section class="card" style="margin-top:1rem;">
        <p style="margin:0;">Todavía no has guardado ninguna oferta como favorita.</p>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-primary" style="display:inline-block; margin-top:1rem;">
            Explorar ofertas
        </a>
    </section>
<?php else: ?>
    <p style="margin:1rem 0; color:#64748b;">
        <?= count($favorites); ?> oferta<?= count($favorites) !== 1 ? 's' : ''; ?> guardada<?= count($favorites) !== 1 ? 's' : ''; ?>
    </p>

    <?php foreach ($favorites as $job): ?>
        <div class="job-card">
            <div class="job-card-header">
                <div style="display:flex; align-items:center; gap:0.85rem;">
                    <div class="company-avatar">
                        <?php if (!empty($job['company_logo'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($job['company_logo']); ?>"
                                 alt="<?= htmlspecialchars($job['company_name']); ?>">
                        <?php else: ?>
                            <!-- Sin logo mostramos la primera letra del nombre (mb_ soporta tildes y ñ) -->
                            <?= mb_strtoupper(mb_substr($job['company_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="job-title">
                            <a href="<?= BASE_URL; ?>/job-detail.php?id=<?= $job['id']; ?>"
                               style="color:inherit; text-decoration:none;">
                                <?= htmlspecialchars($job['title']); ?>
                            </a>
                        </h2>
                        <p class="job-company"><?= htmlspecialchars($job['company_name']); ?></p>
                    </div>
                </div>
                <?php if ($job['status'] === 'closed'): ?>
                    <span class="badge badge-admin">Cerrada</span>
                <?php else: ?>
                    <span class="badge badge-candidate"><?= htmlspecialchars($job['category_name']); ?></span>
                <?php endif; ?>
            </div>

            <div class="job-meta">
                <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                <span>🕐 <?= $workdayLabels[$job['workday']] ?? $job['workday']; ?></span>
                <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>
                <?php if ($job['salary_min'] !== null && $job['salary_max'] !== null): ?>
                    <span>💶 <?= number_format((float)$job['salary_min'], 0, ',', '.'); ?>
                          – <?= number_format((float)$job['salary_max'], 0, ',', '.'); ?>
                          <?= htmlspecialchars($job['currency']); ?></span>
                <?php endif; ?>
            </div>

            <div class="job-actions">
                <?php if ($job['status'] === 'published'): ?>
                    <a href="<?= BASE_URL; ?>/job-detail.php?id=<?= $job['id']; ?>" class="btn-primary">
                        Ver oferta
                    </a>
                <?php else: ?>
                    <span style="color:#94a3b8; font-size:0.88rem;">Oferta cerrada</span>
                <?php endif; ?>

                <button class="btn-favorite" data-job-id="<?= $job['id']; ?>"
                        data-favorited="1"
                        style="background:none; border:none; cursor:pointer; color:#ef4444; font-size:1.2rem; margin-left:auto;"
                        title="Quitar de favoritos">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
