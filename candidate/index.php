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

// =========================================================
// ESTADÍSTICAS DEL CANDIDATO
// =========================================================
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

// =========================================================
// MIS CANDIDATURAS (últimas 5)
// =========================================================
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

$modalityLabels = [
    'onsite' => 'Presencial',
    'hybrid' => 'Híbrido',
    'remote' => 'Remoto',
];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Saludo personalizado con el nombre del candidato -->
<div class="admin-welcome">
    <h1>Panel de candidato</h1>
    <p>Bienvenido, <?= htmlspecialchars($user['full_name']); ?></p>
</div>

<!-- =========================================================
     TARJETAS DE ESTADÍSTICAS
     Resumen rápido de la actividad del candidato.
========================================================= -->
<div class="stats-row" style="margin-top:20px;">

    <div class="admin-stat">
        <div class="admin-stat-icon">📨</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $totalApplications; ?></div>
            <div class="admin-stat-label">Candidaturas enviadas</div>
        </div>
    </div>

    <div class="admin-stat">
        <div class="admin-stat-icon">⏳</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $pendingApplications; ?></div>
            <div class="admin-stat-label">Pendientes de respuesta</div>
        </div>
    </div>

    <div class="admin-stat">
        <div class="admin-stat-icon">✅</div>
        <div class="admin-stat-text">
            <div class="admin-stat-value"><?= $acceptedApplications; ?></div>
            <div class="admin-stat-label">Candidaturas aceptadas</div>
        </div>
    </div>

</div>

<!-- =========================================================
     ACCESOS RÁPIDOS
========================================================= -->
<div class="admin-grid" style="margin-top: 1rem;">
    <a href="<?= BASE_URL; ?>/jobs.php" class="admin-card">
        <div class="admin-card-icon">🔍</div>
        <h2>Explorar ofertas</h2>
        <p>Busca y filtra todas las ofertas disponibles.</p>
    </a>
    <a href="<?= BASE_URL; ?>/candidate/my-applications.php" class="admin-card">
        <div class="admin-card-icon">📋</div>
        <h2>Mis candidaturas</h2>
        <p>Consulta el estado de tus candidaturas enviadas.</p>
    </a>
    <a href="<?= BASE_URL; ?>/candidate/profile-edit.php" class="admin-card">
        <div class="admin-card-icon">👤</div>
        <h2>Mi perfil</h2>
        <p>Actualiza tus datos personales y tu CV.</p>
    </a>
</div>

<!-- =========================================================
     MIS ÚLTIMAS CANDIDATURAS
========================================================= -->
<section class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2>Resumen</h2>
        <?php if (!empty($myApplications)): ?>
            <a href="<?= BASE_URL; ?>/candidate/my-applications.php" class="btn-link">Ver todas →</a>
        <?php endif; ?>
    </div>

    <?php if (empty($myApplications)): ?>
        <p style="color:#64748b;">Todavía no has aplicado a ninguna oferta.</p>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-primary" style="margin-top:0.75rem; display:inline-block;">
            Explorar ofertas
        </a>
    <?php else: ?>
        <table class="panel-table">
            <thead>
                <tr>
                    <th>Oferta</th>
                    <th>Empresa</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myApplications as $app): ?>
                    <tr>
                        <td style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($app['job_title']); ?></td>
                        <td><?= htmlspecialchars($app['company_name']); ?></td>
                        <td style="color:#94a3b8; font-size:0.85rem;"><?= date('d/m/Y', strtotime($app['applied_at'])); ?></td>
                        <td>
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
