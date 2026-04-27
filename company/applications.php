<?php
// company/applications.php — Candidaturas recibidas para una oferta concreta

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Obtenemos el perfil de empresa
$stmtCompany = $pdo->prepare("SELECT * FROM companies WHERE owner_user_id = :uid");
$stmtCompany->execute(['uid' => $user['id']]);
$company = $stmtCompany->fetch();

if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// ID de la oferta cuyas candidaturas queremos ver
$jobId = (int) ($_GET['job'] ?? 0);

if ($jobId === 0) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

// Verificamos que la oferta pertenece a esta empresa
$stmtJob = $pdo->prepare("
    SELECT id, title FROM jobs WHERE id = :id AND company_id = :cid
");
$stmtJob->execute(['id' => $jobId, 'cid' => $company['id']]);
$job = $stmtJob->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

// =========================================================
// PROCESAMOS EL CAMBIO DE ESTADO DE UNA CANDIDATURA
// =========================================================
// La empresa puede cambiar el estado de cada candidatura:
// sent → reviewed → accepted / rejected
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $newStatus     = trim($_POST['status']           ?? '');

    $validStatuses = ['reviewed', 'accepted', 'rejected'];

    if ($applicationId > 0 && in_array($newStatus, $validStatuses)) {
        // Actualizamos el estado. Usamos el job_id para asegurarnos
        // de que la candidatura pertenece a una oferta de esta empresa.
        $stmtUpdate = $pdo->prepare("
            UPDATE applications a
            JOIN jobs j ON j.id = a.job_id
            SET a.status = :status
            WHERE a.id = :aid
              AND j.company_id = :cid
        ");
        $stmtUpdate->execute([
            'status' => $newStatus,
            'aid'    => $applicationId,
            'cid'    => $company['id'],
        ]);
    }

    // Redirigimos para evitar reenvío del formulario (patrón POST-Redirect-GET)
    header('Location: ' . BASE_URL . '/company/applications.php?job=' . $jobId);
    exit;
}

// =========================================================
// OBTENEMOS LAS CANDIDATURAS DE ESTA OFERTA
// =========================================================
$stmtApps = $pdo->prepare("
    SELECT
        a.id,
        a.status,
        a.message,
        a.cv_pdf_path,
        a.applied_at,
        u.full_name  AS candidate_name,
        u.email      AS candidate_email
    FROM applications a
    JOIN users u ON u.id = a.candidate_user_id
    WHERE a.job_id = :jid
    ORDER BY a.applied_at ASC
");
$stmtApps->execute(['jid' => $jobId]);
$applications = $stmtApps->fetchAll();

// Etiquetas y clases para los estados de candidatura
$statusLabels = [
    'sent'     => 'Enviada',
    'reviewed' => 'Revisada',
    'accepted' => 'Aceptada',
    'rejected' => 'Rechazada',
];

$statusClass = [
    'sent'     => 'badge-candidate',
    'reviewed' => 'badge-company',
    'accepted' => 'badge-active',
    'rejected' => 'badge-rejected',
];

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/company/jobs.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver a mis ofertas
</a>

<section class="card">
    <h1>Candidaturas para «<?= htmlspecialchars($job['title']); ?>»</h1>
    <p><?= count($applications); ?> candidatura<?= count($applications) !== 1 ? 's' : ''; ?> recibida<?= count($applications) !== 1 ? 's' : ''; ?></p>
</section>

<?php if (empty($applications)): ?>
    <section class="card" style="margin-top:1rem;">
        <p>Todavía no hay candidaturas para esta oferta.</p>
    </section>

<?php else: ?>
    <?php foreach ($applications as $app): ?>
        <div class="job-card" id="app-<?= $app['id']; ?>">
            <div class="job-card-header">
                <div>
                    <!-- Nombre y email del candidato -->
                    <h2 class="job-title"><?= htmlspecialchars($app['candidate_name']); ?></h2>
                    <p class="job-company"><?= htmlspecialchars($app['candidate_email']); ?></p>
                </div>
                <!-- Estado actual de la candidatura -->
                <span class="badge <?= $statusClass[$app['status']] ?? 'badge-candidate'; ?>">
                    <?= $statusLabels[$app['status']] ?? $app['status']; ?>
                </span>
            </div>

            <div class="job-meta">
                <span>📅 Aplicó el <?= date('d/m/Y', strtotime($app['applied_at'])); ?></span>
                <?php if (!empty($app['cv_pdf_path'])): ?>
                    <a href="<?= BASE_URL . '/' . htmlspecialchars($app['cv_pdf_path']); ?>"
                       target="_blank"
                       class="btn-edit"
                       style="font-size:0.8rem; padding:0.2rem 0.6rem;">
                        📄 Descargar CV
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mensaje del candidato si lo adjuntó -->
            <?php if (!empty($app['message'])): ?>
                <details style="margin-top:0.75rem;">
                    <summary style="cursor:pointer; color:#6366f1; font-size:0.9rem;">
                        Ver mensaje del candidato
                    </summary>
                    <p style="margin-top:0.5rem; color:#374151; white-space:pre-line; font-size:0.9rem;">
                        <?= htmlspecialchars($app['message']); ?>
                    </p>
                </details>
            <?php endif; ?>

            <!-- Formulario para cambiar el estado de la candidatura -->
            <?php if ($app['status'] !== 'accepted' && $app['status'] !== 'rejected'): ?>
                <form method="post"
                      action="<?= BASE_URL; ?>/company/applications.php?job=<?= $jobId; ?>"
                      style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <input type="hidden" name="application_id" value="<?= $app['id']; ?>">

                    <?php if ($app['status'] === 'sent'): ?>
                        <button type="submit" name="status" value="reviewed" class="btn-edit">
                            Marcar como revisada
                        </button>
                    <?php endif; ?>

                    <?php if (in_array($app['status'], ['sent', 'reviewed'])): ?>
                        <button type="submit" name="status" value="accepted"
                                class="btn-accept"
                                onclick="return confirm('¿Aceptar la candidatura de <?= htmlspecialchars(addslashes($app['candidate_name'])); ?>?');">
                            Aceptar
                        </button>
                        <button type="submit" name="status" value="rejected"
                                class="btn-delete"
                                onclick="return confirm('¿Rechazar la candidatura de <?= htmlspecialchars(addslashes($app['candidate_name'])); ?>?');">
                            Rechazar
                        </button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
