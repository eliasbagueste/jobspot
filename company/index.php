<?php
// company/index.php — Panel principal de la empresa
// Si la empresa no ha completado su perfil, muestra el formulario de configuración.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// =========================================================
// COMPROBAMOS SI LA EMPRESA YA TIENE PERFIL CREADO
// =========================================================
// Un usuario con rol 'company' puede existir sin tener todavía
// un registro en la tabla companies. En ese caso mostramos
// el formulario de configuración inicial.
$stmtCompany = $pdo->prepare("
    SELECT * FROM companies WHERE owner_user_id = :uid
");
$stmtCompany->execute(['uid' => $user['id']]);
$company = $stmtCompany->fetch();

$error   = '';
$success = '';

// =========================================================
// FORMULARIO DE CONFIGURACIÓN INICIAL (si no tiene perfil)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$company) {

    $legalName   = trim($_POST['legal_name']  ?? '');
    $brandName   = trim($_POST['brand_name']  ?? '');
    $location    = trim($_POST['location']    ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validación: nombre legal y nombre comercial son obligatorios
    if ($legalName === '' || $brandName === '') {
        $error = 'El nombre legal y el nombre comercial son obligatorios.';
    } else {
        // Creamos el perfil de empresa vinculado al usuario actual
        // is_verified = 0 hasta que el administrador lo verifique
        $stmtInsert = $pdo->prepare("
            INSERT INTO companies (owner_user_id, legal_name, brand_name, location, description, is_verified)
            VALUES (:owner_user_id, :legal_name, :brand_name, :location, :description, 0)
        ");
        $stmtInsert->execute([
            'owner_user_id' => $user['id'],
            'legal_name'    => $legalName,
            'brand_name'    => $brandName,
            'location'      => $location !== '' ? $location : null,
            'description'   => $description !== '' ? $description : null,
        ]);

        header('Location: ' . BASE_URL . '/company/index.php?created=1');
        exit;
    }
}

// Mensaje de éxito tras redirección POST-GET
if (isset($_GET['created'])) {
    $success = 'Perfil de empresa creado correctamente. El administrador lo revisará pronto.';
}

// Si ya tiene perfil, cargamos las estadísticas del panel
if ($company) {

    // Total de ofertas publicadas por esta empresa
    $stmtPublished = $pdo->prepare("
        SELECT COUNT(*) FROM jobs
        WHERE company_id = :cid AND status = 'published'
    ");
    $stmtPublished->execute(['cid' => $company['id']]);
    $publishedJobs = (int) $stmtPublished->fetchColumn();

    // Total de candidaturas recibidas en todas sus ofertas
    $stmtApplications = $pdo->prepare("
        SELECT COUNT(*) FROM applications a
        JOIN jobs j ON j.id = a.job_id
        WHERE j.company_id = :cid
    ");
    $stmtApplications->execute(['cid' => $company['id']]);
    $totalApplications = (int) $stmtApplications->fetchColumn();

    // Candidaturas pendientes de decisión (sent + reviewed)
    $stmtDecisionCount = $pdo->prepare("
        SELECT COUNT(*) FROM applications a
        JOIN jobs j ON j.id = a.job_id
        WHERE j.company_id = :cid AND a.status IN ('sent', 'reviewed')
    ");
    $stmtDecisionCount->execute(['cid' => $company['id']]);
    $pendingDecisionCount = (int) $stmtDecisionCount->fetchColumn();

    // Candidaturas sin abrir (status = 'sent')
    $stmtPendingApps = $pdo->prepare("
        SELECT
            a.id         AS application_id,
            a.applied_at,
            j.id         AS job_id,
            j.title      AS job_title,
            u.full_name  AS candidate_name,
            u.email      AS candidate_email
        FROM applications a
        JOIN jobs j  ON j.id  = a.job_id
        JOIN users u ON u.id  = a.candidate_user_id
        WHERE j.company_id = :cid AND a.status = 'sent'
        ORDER BY a.applied_at ASC
        LIMIT 5
    ");
    $stmtPendingApps->execute(['cid' => $company['id']]);
    $appsPendingReview = $stmtPendingApps->fetchAll();

}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!$company): ?>
    <!-- =====================================================
         FORMULARIO DE CONFIGURACIÓN INICIAL
         Se muestra cuando el usuario company aún no tiene
         un perfil de empresa registrado.
    ====================================================== -->
    <section class="card">
        <h1>Configura tu empresa</h1>
        <p>Para poder publicar ofertas primero debes completar el perfil de tu empresa.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!$company): ?>
            <form method="post" action="<?= BASE_URL; ?>/company/index.php" class="auth-form" novalidate>

                <div class="form-group">
                    <label for="legal_name">Nombre legal (razón social) *</label>
                    <input type="text" id="legal_name" name="legal_name"
                           value="<?= htmlspecialchars($_POST['legal_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="brand_name">Nombre comercial *</label>
                    <input type="text" id="brand_name" name="brand_name"
                           value="<?= htmlspecialchars($_POST['brand_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="location">Ubicación</label>
                    <input type="text" id="location" name="location"
                           placeholder="Ej: Barcelona, Madrid..."
                           value="<?= htmlspecialchars($_POST['location'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Descripción de la empresa</label>
                    <textarea id="description" name="description" rows="4"
                              style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;"
                              placeholder="Cuéntanos a qué se dedica tu empresa..."><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Crear perfil de empresa</button>
            </form>

            <script>
            document.querySelector('.auth-form').addEventListener('submit', function (e) {
                document.querySelectorAll('.field-error').forEach(el => el.remove());
                document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

                let valid = true;

                function error(input, msg) {
                    valid = false;
                    input.classList.add('input-error');
                    const span = document.createElement('span');
                    span.className = 'field-error';
                    span.textContent = msg;
                    input.closest('.form-group').appendChild(span);
                }

                const legalName = document.getElementById('legal_name');
                const brandName = document.getElementById('brand_name');

                if (!legalName.value.trim())
                    error(legalName, 'El nombre legal es obligatorio.');

                if (!brandName.value.trim())
                    error(brandName, 'El nombre comercial es obligatorio.');

                if (!valid) e.preventDefault();
            });
            </script>
        <?php endif; ?>
    </section>

<?php endif; ?>

<?php if ($company): ?>
    <!-- =====================================================
         PANEL PRINCIPAL — visible solo si ya tiene perfil
    ====================================================== -->
    <?php if ($success !== ''): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="admin-welcome admin-welcome--avatar">
        <div class="company-avatar company-avatar--lg">
            <?php if (!empty($company['logo_path'])): ?>
                <img src="<?= BASE_URL . '/' . htmlspecialchars($company['logo_path']); ?>" alt="Logo">
            <?php else: ?>
                <?= mb_strtoupper(mb_substr($company['brand_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
            <?php endif; ?>
        </div>

        <div>
            <h1 style="margin:0;"><?= htmlspecialchars($company['brand_name']); ?></h1>
            <p style="margin:0;">Panel de control</p>
            <?php if (!(bool)$company['is_verified']): ?>
                <div class="alert alert-error" style="margin-top:0.75rem;">
                    Tu empresa está pendiente de verificación por el administrador.
                    Podrás publicar ofertas una vez que sea aprobada.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-row" style="margin-top:20px;">
        <div class="admin-stat">
            <div class="admin-stat-icon">📢</div>
            <div class="admin-stat-text">
                <div class="admin-stat-value"><?= $publishedJobs; ?></div>
                <div class="admin-stat-label">Ofertas publicadas</div>
            </div>
        </div>
<div class="admin-stat">
            <div class="admin-stat-icon">👥</div>
            <div class="admin-stat-text">
                <div class="admin-stat-value"><?= $totalApplications; ?></div>
                <div class="admin-stat-label">Candidaturas recibidas</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">⏳</div>
            <div class="admin-stat-text">
                <div class="admin-stat-value"><?= $pendingDecisionCount; ?></div>
                <div class="admin-stat-label">Candidaturas pendientes de decisión</div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="admin-grid" style="margin-top: 1rem;">
        <a href="<?= BASE_URL; ?>/company/job-create.php" class="admin-card">
            <div class="admin-card-icon">➕</div>
            <h2>Crear oferta</h2>
            <p>Publica una nueva oferta de empleo.</p>
        </a>
        <a href="<?= BASE_URL; ?>/company/jobs.php" class="admin-card">
            <div class="admin-card-icon">📋</div>
            <h2>Mis ofertas</h2>
            <p>Gestiona tus ofertas publicadas.</p>
        </a>
        <a href="<?= BASE_URL; ?>/company/profile-edit.php" class="admin-card">
            <div class="admin-card-icon">🏢</div>
            <h2>Editar perfil</h2>
            <p>Actualiza los datos de tu empresa.</p>
        </a>
    </div>
<?php endif; ?>

<?php if ($company): ?>
    <section class="card" style="margin-top:1.5rem;">
        <div class="card-header">
            <h2>
                Candidaturas pendientes de revisar
                <?php if (count($appsPendingReview) > 0): ?>
                    <span class="notif-badge"><?= count($appsPendingReview); ?></span>
                <?php endif; ?>
            </h2>
            <a href="<?= BASE_URL; ?>/company/jobs.php" class="btn-link">Ver mis ofertas →</a>
        </div>

        <table class="panel-table">
            <thead>
                <tr>
                    <th>Candidato</th>
                    <th>Oferta</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appsPendingReview)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; color:#94a3b8;">
                            No hay candidaturas pendientes de revisar.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($appsPendingReview as $app): ?>
                    <tr>
                        <td>
                            <span style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($app['candidate_name']); ?></span><br>
                            <span style="font-size:0.8rem; color:#94a3b8;"><?= htmlspecialchars($app['candidate_email']); ?></span>
                        </td>
                        <td><?= htmlspecialchars($app['job_title']); ?></td>
                        <td style="color:#94a3b8; font-size:0.85rem;"><?= date('d/m/Y', strtotime($app['applied_at'])); ?></td>
                        <td>
                            <a href="<?= BASE_URL; ?>/company/applications.php?job=<?= $app['job_id']; ?>#app-<?= $app['application_id']; ?>"
                               class="btn-edit" style="font-size:0.82rem; padding:0.3rem 0.75rem;">
                                Revisar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
