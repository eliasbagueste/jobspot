<?php
// candidate/index.php — Panel principal del candidato

// Cargamos la configuración y la conexión a la base de datos
require_once __DIR__ . '/../config/database.php';

// Cargamos las funciones de autenticación
require_once __DIR__ . '/../includes/auth.php';

// Redirigimos al login si no hay sesión activa
requireLogin();

// Solo los usuarios con rol 'candidate' pueden acceder; si no, mostramos el 403
requireRole('candidate');

// Guardamos el usuario de sesión en una variable local
$user = $_SESSION['user'];
$pdo  = getPDO();

// Estadísticas del candidato para mostrar en el panel
// Contamos cuántas candidaturas ha enviado en total
$stmtTotal = $pdo->prepare("
    SELECT COUNT(*) FROM applications WHERE candidate_user_id = :uid
");
$stmtTotal->execute(['uid' => $user['id']]);
$totalApplications = (int) $stmtTotal->fetchColumn();

// Contamos cuántas candidaturas están pendientes (enviadas pero no revisadas)
$stmtPending = $pdo->prepare("
    SELECT COUNT(*) FROM applications
    WHERE candidate_user_id = :uid AND status = 'sent'
");
$stmtPending->execute(['uid' => $user['id']]);
$pendingApplications = (int) $stmtPending->fetchColumn();

// Contamos cuántas candidaturas han sido aceptadas
$stmtAccepted = $pdo->prepare("
    SELECT COUNT(*) FROM applications
    WHERE candidate_user_id = :uid AND status = 'accepted'
");
$stmtAccepted->execute(['uid' => $user['id']]);
$acceptedApplications = (int) $stmtAccepted->fetchColumn();

// Últimas 5 candidaturas del candidato (para el resumen del panel)
$stmtApps = $pdo->prepare("
    SELECT
        a.id,
        a.status,
        a.applied_at,
        j.id        AS job_id,
        j.title     AS job_title,
        j.location,
        j.modality,
        co.brand_name AS company_name
    FROM applications a
    JOIN jobs      j  ON j.id  = a.job_id
    JOIN companies co ON co.id = j.company_id
    WHERE a.candidate_user_id = :uid
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmtApps->execute(['uid' => $user['id']]);
$myApplications = $stmtApps->fetchAll();

// Texto legible para cada estado de candidatura (para mostrar en la tabla)
$statusLabels = [
    'sent'     => 'Sent',
    'reviewed' => 'Reviewed',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
];

// Clase CSS para el badge de color según el estado
$statusClass = [
    'sent'     => 'badge-candidate',  // azul
    'reviewed' => 'badge-company',    // morado
    'accepted' => 'badge-active',     // verde
    'rejected' => 'badge-rejected',   // rojo
];

// Texto legible para la modalidad de trabajo
$modalityLabels = [
    'onsite' => 'On-site',
    'hybrid' => 'Hybrid',
    'remote' => 'Remote',
];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Saludo personalizado con el nombre del candidato -->
<div class="admin-welcome">
    <h1>Candidate dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($user['full_name']); ?></p>
</div>

<!-- Tarjetas de resumen rápido: candidaturas enviadas, pendientes y aceptadas -->
<div class="stats-row" style="margin-top:20px;">

    <div class="admin-stat">
        <div class="admin-stat-icon">📨</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $totalApplications; ?></div>
            <div class="admin-stat-label">Applications sent</div>
        </div>
    </div>

    <div class="admin-stat">
        <div class="admin-stat-icon">⏳</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $pendingApplications; ?></div>
            <div class="admin-stat-label">Awaiting response</div>
        </div>
    </div>

    <div class="admin-stat">
        <div class="admin-stat-icon">✅</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $acceptedApplications; ?></div>
            <div class="admin-stat-label">Applications accepted</div>
        </div>
    </div>

</div>

<!-- Accesos rápidos del candidato -->
<div class="admin-grid" style="margin-top: 1rem;">
    <a href="<?= BASE_URL; ?>/jobs.php" class="admin-card">
        <div class="admin-card-icon">🔍</div>
        <h2>Browse jobs</h2>
        <p>Search and filter all available job listings.</p>
    </a>
    <a href="<?= BASE_URL; ?>/candidate/my-applications.php" class="admin-card">
        <div class="admin-card-icon">📋</div>
        <h2>My applications</h2>
        <p>Check the status of your submitted applications.</p>
    </a>
    <a href="<?= BASE_URL; ?>/candidate/profile-edit.php" class="admin-card">
        <div class="admin-card-icon">👤</div>
        <h2>My profile</h2>
        <p>Update your personal details and CV.</p>
    </a>
</div>

<!-- Últimas candidaturas enviadas por el candidato -->
<section class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>Summary</h2>
        <?php if (!empty($myApplications)): ?>
            <a href="<?= BASE_URL; ?>/candidate/my-applications.php" class="btn-link">View all →</a>
        <?php endif; ?>
    </div>

    <?php if (empty($myApplications)): ?>
        <p style="color:#64748b;">You have not applied to any jobs yet.</p>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-primary" style="margin-top:0.75rem; display:inline-block;">
            Browse jobs
        </a>
    <?php else: ?>
        <table class="panel-table">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myApplications as $app): ?>
                    <tr>
                        <td style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($app['job_title']); ?></td>
                        <td><?= htmlspecialchars($app['company_name']); ?></td>
                        <!-- date() formatea la fecha de la BD a d/m/Y; strtotime() la convierte primero a timestamp -->
                        <td style="color:#94a3b8; font-size:0.85rem;"><?= date('d/m/Y', strtotime($app['applied_at'])); ?></td>
                        <td>
                            <!-- ?? 'badge-candidate' es el valor por defecto si el estado no está en el array -->
                            <span class="badge <?= $statusClass[$app['status']] ?? 'badge-candidate'; ?>">
                                <?= $statusLabels[$app['status']] ?? $app['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
