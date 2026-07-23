<?php
// candidate/profile-edit.php — Editar perfil del candidato

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('candidate');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Cargamos el perfil del candidato si existe
$stmtProfile = $pdo->prepare("SELECT * FROM candidate_profiles WHERE user_id = :uid");
$stmtProfile->execute(['uid' => $user['id']]);
$profile = $stmtProfile->fetch();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']       ?? '');
    $phone    = trim($_POST['phone']           ?? '');
    $city     = trim($_POST['city']            ?? '');
    $summary  = trim($_POST['profile_summary'] ?? '');
    $cvPath   = $profile['cv_pdf_path'] ?? null;

    if ($fullName === '') {
        $error = 'Full name is required.';
    }

    // Procesamos el PDF si el candidato lo ha subido
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['cv'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error uploading the file. Please try again.';
        } elseif ($file['type'] !== 'application/pdf') {
            $error = 'Only PDF files are accepted.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'The file cannot exceed 5 MB.';
        } else {
            $uploadsDir = __DIR__ . '/../uploads/cvs/';
            // Si la carpeta no existe la creamos con permisos 0755
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            // time() en el nombre evita sobrescribir si sube varios CVs seguidos
            $filename   = 'profile_cv_' . $user['id'] . '_' . time() . '.pdf';
            // Mueve el archivo del directorio temporal del servidor a nuestra carpeta
            if (move_uploaded_file($file['tmp_name'], $uploadsDir . $filename)) {
                $cvPath = 'uploads/cvs/' . $filename;
            } else {
                $error = 'Could not save the file. Check the permissions on the uploads/cvs/ folder.';
            }
        }
    }

    if ($error === '') {
        // Actualizamos el nombre en la tabla users (está separado del perfil extendido)
        $stmtName = $pdo->prepare("UPDATE users SET full_name = :name WHERE id = :uid");
        $stmtName->execute(['name' => $fullName, 'uid' => $user['id']]);
        // También actualizamos la sesión para que el cambio se refleje de inmediato sin relogin
        $_SESSION['user']['full_name'] = $fullName;
        $user['full_name'] = $fullName;

        // Si ya existía un perfil extendido lo actualizamos; si no, lo creamos (INSERT)
        if ($profile) {
            $stmtUpdate = $pdo->prepare("
                UPDATE candidate_profiles
                SET phone           = :phone,
                    city            = :city,
                    profile_summary = :summary,
                    cv_pdf_path     = :cv
                WHERE user_id = :uid
            ");
            $stmtUpdate->execute([
                // Si el campo viene vacío guardamos NULL en la BD, no una cadena vacía
                'phone'   => $phone !== '' ? $phone : null,
                'city'    => $city !== '' ? $city : null,
                'summary' => $summary !== '' ? $summary : null,
                'cv'      => $cvPath,
                'uid'     => $user['id'],
            ]);
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO candidate_profiles (user_id, phone, city, profile_summary, cv_pdf_path)
                VALUES (:uid, :phone, :city, :summary, :cv)
            ");
            $stmtInsert->execute([
                'uid'     => $user['id'],
                'phone'   => $phone !== '' ? $phone : null,
                'city'    => $city !== '' ? $city : null,
                'summary' => $summary !== '' ? $summary : null,
                'cv'      => $cvPath,
            ]);
        }

        // Recargamos el perfil actualizado
        $stmtProfile->execute(['uid' => $user['id']]);
        $profile = $stmtProfile->fetch();
        $success = 'Profile updated successfully.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/candidate/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to dashboard
</a>

<section class="auth-box">
    <h1>My profile</h1>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/candidate/profile-edit.php"
          class="auth-form" enctype="multipart/form-data" novalidate id="form-profile">

        <div class="form-group">
            <label for="full_name">Full name *</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($user['full_name']); ?>">
        </div>

        <div class="form-group">
            <label>Email address</label>
            <!-- El email está desactivado (disabled) porque no se puede cambiar desde aquí -->
            <input type="email" value="<?= htmlspecialchars($user['email']); ?>" disabled
                   style="background:#f8fafc; color:#64748b;">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone"
                   placeholder="e.g. 600 123 456"
                   value="<?= htmlspecialchars($profile['phone'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city"
                   placeholder="e.g. Dublin, Cork..."
                   value="<?= htmlspecialchars($profile['city'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="profile_summary">About me</label>
            <textarea id="profile_summary" name="profile_summary" rows="5"
                      style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;"
                      placeholder="Tell us about your experience, skills and career goals..."><?= htmlspecialchars($profile['profile_summary'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="cv">CV in PDF format</label>
            <?php if (!empty($profile['cv_pdf_path'])): ?>
                <p style="margin-bottom:0.5rem; font-size:0.9rem; color:#374151;">
                    Current CV:
                    <a href="<?= BASE_URL . '/' . htmlspecialchars($profile['cv_pdf_path']); ?>"
                       target="_blank" class="btn-link">
                        📄 View CV
                    </a>
                </p>
            <?php endif; ?>
            <input type="file" id="cv" name="cv" accept=".pdf"
                   style="display:block; margin-top:4px;">
            <small style="color:#64748b; display:block; margin-top:4px;">
                Maximum 5 MB. Upload a new PDF to replace the current one.
            </small>
        </div>

        <button type="submit" class="btn-primary">Save changes</button>
        <a href="<?= BASE_URL; ?>/candidate/index.php" class="btn-link btn-cancel">Cancel</a>
    </form>

    <script>
    // Validación en el cliente: comprobamos nombre y formato del PDF antes de enviar
    document.getElementById('form-profile').addEventListener('submit', function (e) {
        document.querySelectorAll('.field-error').forEach(el => el.remove());
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        let valid = true;

        function fieldError(input, msg) {
            valid = false;
            input.classList.add('input-error');
            const span = document.createElement('span');
            span.className = 'field-error';
            span.textContent = msg;
            input.closest('.form-group').appendChild(span);
        }

        const fullName = document.getElementById('full_name');
        if (!fullName.value.trim())
            fieldError(fullName, 'Full name is required.');

        const cv = document.getElementById('cv');
        if (cv.files.length > 0) {
            const file = cv.files[0];
            if (file.type !== 'application/pdf')
                fieldError(cv, 'Only PDF files are accepted.');
            else if (file.size > 5 * 1024 * 1024)
                fieldError(cv, 'The file cannot exceed 5 MB.');
        }

        if (!valid) e.preventDefault();
    });
    </script>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
