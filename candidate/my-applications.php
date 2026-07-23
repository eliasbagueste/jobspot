<?php
// candidate/my-applications.php — Listado de candidaturas enviadas por el candidato

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('candidate');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Si el candidato pulsa "Retirar candidatura", procesamos el borrado aquí
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_id'])) {
    $withdrawId = (int) $_POST['withdraw_id'];

    // Solo borramos si pertenece al candidato y aún no tiene decisión final
    $stmtDel = $pdo->prepare("
        DELETE FROM applications
        WHERE id = :id AND candidate_user_id = :uid AND status IN ('sent', 'reviewed')
    ");
    $stmtDel->execute(['id' => $withdrawId, 'uid' => $user['id']]);

    header('Location: ' . BASE_URL . '/candidate/my-applications.php?withdrawn=1');
    exit;
}

// Cargamos todas las candidaturas del candidato con datos de oferta y empresa
// Hacemos JOIN con jobs para obtener el título de la oferta,
// y con companies para obtener el nombre de la empresa.
// Ordenamos de más reciente a más antigua.
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.status,
        a.message,
        a.applied_at,
        j.title          AS job_title,
        j.location       AS job_location,
        j.id             AS job_id,
        co.brand_name    AS company_name
    FROM applications a
    JOIN jobs      j   ON j.id  = a.job_id
    JOIN companies co  ON co.id = j.company_id
    WHERE a.candidate_user_id = :uid
    ORDER BY a.applied_at DESC
");
$stmt->execute(['uid' => $user['id']]);
$applications = $stmt->fetchAll();

// Etiquetas y clases CSS para cada estado de candidatura
// Cada estado tiene un texto legible y un color diferente para identificarlo visualmente
$statusLabels = [
    'sent'     => 'Sent',
    'reviewed' => 'Reviewed',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
];

// Clase CSS para colorear el badge según el estado
$statusClass = [
    'sent'     => 'badge-candidate',  // azul (neutro)
    'reviewed' => 'badge-company',    // morado
    'accepted' => 'badge-active',     // verde
    'rejected' => 'badge-rejected',   // rojo
];

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/candidate/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to dashboard
</a>
<div class="admin-welcome">
    <h1>My applications</h1>
    <p>Here you can check the status of every application you have sent.</p>
</div>

<?php if (isset($_GET['withdrawn'])): ?>
    <div class="alert alert-success" style="margin-top:1rem;">
        Application withdrawn successfully.
    </div>
<?php endif; ?>

<?php if (empty($applications)): ?>
    <!-- Todavía no ha enviado ninguna candidatura -->
    <section class="card" style="margin-top: 1rem;">
        <p>You have not sent any applications yet.</p>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-primary" style="margin-top: 1rem; display: inline-block;">
            View available jobs
        </a>
    </section>

<?php else: ?>
    <p style="margin: 1rem 0; color: #64748b;">
        Total: <?= count($applications); ?> application<?= count($applications) !== 1 ? 's' : ''; ?>
    </p>

    <?php foreach ($applications as $app): ?>
        <div class="job-card">
            <div class="job-card-header">
                <div>
                    <!-- Título de la oferta y empresa -->
                    <h2 class="job-title"><?= htmlspecialchars($app['job_title']); ?></h2>
                    <p class="job-company"><?= htmlspecialchars($app['company_name']); ?></p>
                </div>

                <!-- Badge con el estado actual de la candidatura -->
                <span class="badge <?= $statusClass[$app['status']] ?? 'badge-candidate'; ?>">
                    <?= $statusLabels[$app['status']] ?? $app['status']; ?>
                </span>
            </div>

            <div class="job-meta">
                <!-- Fecha en que se envió la candidatura -->
                <span>📅 Sent on <?= date('d/m/Y', strtotime($app['applied_at'])); ?></span>
                <span>📍 <?= htmlspecialchars($app['job_location']); ?></span>
            </div>

            <!-- Mensaje que el candidato adjuntó al aplicar (si lo puso) -->
            <?php if (!empty($app['message'])): ?>
                <details style="margin-top: 0.75rem;">
                    <summary style="cursor: pointer; color: #6366f1; font-size: 0.9rem;">
                        View sent message
                    </summary>
                    <p style="margin-top: 0.5rem; color: #374151; white-space: pre-line; font-size: 0.9rem;">
                        <?= htmlspecialchars($app['message']); ?>
                    </p>
                </details>
            <?php endif; ?>

            <!-- Botón retirar — solo si está pendiente de decisión -->
            <?php if (in_array($app['status'], ['sent', 'reviewed'])): ?>
                <form method="post" action="<?= BASE_URL; ?>/candidate/my-applications.php"
                      style="margin-top:0.75rem;"
                      onsubmit="return confirm('Are you sure you want to withdraw your application for “<?= htmlspecialchars($app['job_title']); ?>”? This action cannot be undone.');">
                    <input type="hidden" name="withdraw_id" value="<?= $app['id']; ?>">
                    <button type="submit" class="btn-delete" style="font-size:0.85rem; padding:0.3rem 0.8rem;">
                        Withdraw application
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
