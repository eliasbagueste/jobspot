<?php
// candidate/apply.php — Formulario para aplicar a una oferta de trabajo

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Solo candidatos autenticados pueden acceder a esta página
requireLogin();
requireRole('candidate');

$user   = $_SESSION['user'];
$pdo    = getPDO();

// Obtenemos el ID de la oferta desde la URL y lo casteamos a entero por seguridad
$jobId = (int) ($_GET['job'] ?? 0);

if ($jobId === 0) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit;
}

// Cargamos la oferta — solo si está publicada, para que no se pueda aplicar a cerradas
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
        co.brand_name AS company_name
    FROM jobs j
    JOIN companies co ON co.id = j.company_id
    WHERE j.id = :id AND j.status = 'published'
");
$stmt->execute(['id' => $jobId]);
$job = $stmt->fetch();

// Si la oferta no existe o no está publicada, volvemos al listado
if (!$job) {
    header('Location: ' . BASE_URL . '/jobs.php');
    exit;
}

// Compruebo si ya ha aplicado antes para no mostrar el formulario de nuevo
// (la BD también tiene un UNIQUE, pero así lo controlamos desde PHP)
$stmtCheck = $pdo->prepare("
    SELECT id FROM applications
    WHERE candidate_user_id = :uid AND job_id = :jid
");
$stmtCheck->execute(['uid' => $user['id'], 'jid' => $jobId]);
$alreadyApplied = (bool) $stmtCheck->fetch();

$error   = '';
$success = '';

// Cargamos el perfil del candidato para usar su CV si tiene uno subido
$stmtProfile = $pdo->prepare("SELECT cv_pdf_path FROM candidate_profiles WHERE user_id = :uid");
$stmtProfile->execute(['uid' => $user['id']]);
$candidateProfile = $stmtProfile->fetch();
$profileCvPath = $candidateProfile['cv_pdf_path'] ?? null;

// Solo procesamos el formulario si viene por POST y el candidato aún no ha aplicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyApplied) {

    $message = trim($_POST['message'] ?? '');
    // Por defecto usamos el CV del perfil; si sube uno nuevo lo sobreescribimos
    $cvPath  = $profileCvPath;

    if ($message === '') {
        $error = 'You must explain why you are the ideal candidate for this job.';
    } else {
        // Si el candidato sube un CV nuevo, lo usamos en lugar del del perfil
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['cv'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Error uploading the file. Please try again.';
            } elseif ($file['type'] !== 'application/pdf') {
                $error = 'Only PDF files are accepted.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                // 5 * 1024 * 1024 = 5 MB en bytes
                $error = 'The file cannot exceed 5 MB.';
            } else {
                $uploadsDir = __DIR__ . '/../uploads/cvs/';
                // Si la carpeta no existe la creamos. 0755 = permisos de lectura para todos,
                // escritura solo para el propietario. true = crea carpetas intermedias si hacen falta.
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                // Incluimos time() en el nombre para que dos uploads del mismo candidato
                // en la misma oferta no se sobreescriban entre sí
                $filename   = 'cv_' . $user['id'] . '_' . $jobId . '_' . time() . '.pdf';
                move_uploaded_file($file['tmp_name'], $uploadsDir . $filename);
                $cvPath = 'uploads/cvs/' . $filename;
            }
        }

        if ($error === '') {
            try {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO applications (job_id, candidate_user_id, message, cv_pdf_path, status)
                    VALUES (:job_id, :candidate_user_id, :message, :cv_pdf_path, 'sent')
                ");
                $stmtInsert->execute([
                    'job_id'            => $jobId,
                    'candidate_user_id' => $user['id'],
                    'message'           => $message,
                    'cv_pdf_path'       => $cvPath,
                ]);

                $alreadyApplied = true;
                $success = 'Application sent successfully! The company will review your application soon.';

            } catch (PDOException $e) {
                // El código SQLSTATE '23000' significa violación de restricción única (UNIQUE)
                // Lo capturamos por si dos peticiones simultáneas intentan insertar la misma candidatura
                if ($e->getCode() === '23000') {
                    $error = 'You have already applied to this job.';
                } else {
                    $error = 'Error sending the application. Please try again.';
                }
            }
        }
    }
}

// Etiquetas legibles para los ENUM de la base de datos
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

require_once __DIR__ . '/../includes/header.php';
?>

<?php
// Mostramos el bloque de "candidatura enviada" en dos casos:
// 1. Acaba de enviarla ahora mismo ($success tiene texto)
// 2. Ya la había enviado antes y llega a la página sin haber enviado nada ($alreadyApplied y sin $success)
if ($success !== '' || ($alreadyApplied && $success === '')): ?>
<section class="card">
    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php else: ?>
        <div class="alert alert-error">You have already applied to this job.</div>
    <?php endif; ?>
    <a href="<?= BASE_URL; ?>/candidate/my-applications.php" class="btn-primary" style="display:inline-block; margin-top:1rem;">
        View my applications
    </a>
</section>
<?php endif; ?>

<a href="<?= BASE_URL; ?>/jobs.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to jobs
</a>

<!-- Detalles de la oferta -->
<section class="card">
    <h1><?= htmlspecialchars($job['title']); ?></h1>
    <p class="job-company" style="font-size: 1.1rem; margin-bottom: 1rem;">
        <?= htmlspecialchars($job['company_name']); ?>
    </p>

    <!-- Metadatos de la oferta -->
    <div class="job-meta" style="margin-bottom: 1.5rem;">
        <span>📍 <?= htmlspecialchars($job['location']); ?></span>
        <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
        <span>🕐 <?= $workdayLabels[$job['workday']] ?? $job['workday']; ?></span>
        <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>
        <?php if ($job['salary_min'] !== null && $job['salary_max'] !== null): ?>
            <span>
                💶 <?= number_format((float)$job['salary_min'], 0, ',', '.'); ?>€
                – <?= number_format((float)$job['salary_max'], 0, ',', '.'); ?>€
            </span>
        <?php endif; ?>
    </div>

    <!-- Descripción completa de la oferta -->
    <h2 style="margin-bottom: 0.5rem;">Job description</h2>
    <p style="white-space: pre-line; color: #374151;">
        <?= htmlspecialchars($job['description']); ?>
    </p>
</section>

<!-- Formulario de candidatura: solo si el candidato todavía no ha aplicado -->
<?php if ($success === '' && !$alreadyApplied): ?>
<section class="card" style="margin-top: 1.5rem;">
    <h2>Submit application</h2>
    <p>You can attach a personalised message for the company.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/candidate/apply.php?job=<?= $jobId; ?>"
          class="auth-form" enctype="multipart/form-data" novalidate id="form-apply">

        <div class="form-group">
            <label for="message">Why are you the ideal candidate? *</label>
            <textarea
                id="message"
                name="message"
                rows="5"
                placeholder="Explain your motivation, relevant experience and why you're a good fit for this role..."
                style="width:100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 0.95rem;"
            ><?= htmlspecialchars($_POST['message'] ?? ''); /* repopula el textarea si el formulario da error */ ?></textarea>
        </div>

        <div class="form-group">
            <label for="cv">CV in PDF format</label>
            <?php if ($profileCvPath): ?>
                <p style="margin: 0.25rem 0 0.5rem; font-size:0.875rem; color:#374151;">
                    Your profile CV will be used.
                    <a href="<?= BASE_URL . '/' . htmlspecialchars($profileCvPath); ?>" target="_blank" class="btn-link">
                        View current CV
                    </a>
                </p>
                <p style="margin:0 0 0.4rem; font-size:0.8rem; color:#64748b;">
                    Optional: upload another PDF to use just for this application.
                </p>
            <?php else: ?>
                <p style="margin: 0.25rem 0 0.5rem; font-size:0.875rem; color:#64748b;">
                    You don't have a CV on your profile.
                    <a href="<?= BASE_URL; ?>/candidate/profile-edit.php" class="btn-link">Add CV to profile</a>
                </p>
            <?php endif; ?>
            <input type="file" id="cv" name="cv" accept=".pdf"
                   style="display:block; margin-top:4px;">
        </div>

        <button type="submit" class="btn-primary">Submit application</button>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-link btn-cancel">Cancel</a>
    </form>

    <script>
    // Validación en el cliente antes de enviar para dar feedback inmediato.
    // El servidor también valida por si acaso el JS está desactivado.
    document.getElementById('form-apply').addEventListener('submit', function (e) {
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

        const message = document.getElementById('message');
        const cv      = document.getElementById('cv');

        if (!message.value.trim())
            error(message, 'You must explain why you are the ideal candidate for this job.');

        if (cv.files.length > 0) {
            const file = cv.files[0];
            if (file.type !== 'application/pdf')
                error(cv, 'Only PDF files are accepted.');
            else if (file.size > 5 * 1024 * 1024)
                error(cv, 'The file cannot exceed 5 MB.');
        }

        if (!valid) e.preventDefault();
    });
    </script>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
