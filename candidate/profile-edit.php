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
        $error = 'El nombre completo es obligatorio.';
    }

    // Procesamos el PDF si el candidato lo ha subido
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['cv'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error al subir el archivo. Inténtalo de nuevo.';
        } elseif ($file['type'] !== 'application/pdf') {
            $error = 'Solo se aceptan archivos en formato PDF.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'El archivo no puede superar los 5 MB.';
        } else {
            $uploadsDir = __DIR__ . '/../uploads/cvs/';
            // Si la carpeta no existe la creamos con permisos 0755
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            // time() en el nombre evita sobrescribir si sube varios CVs seguidos
            $filename   = 'profile_cv_' . $user['id'] . '_' . time() . '.pdf';
            // Mueve el archivo del directorio temporal del servidor a nuestra carpeta
            move_uploaded_file($file['tmp_name'], $uploadsDir . $filename);
            $cvPath = 'uploads/cvs/' . $filename;
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
        $success = 'Perfil actualizado correctamente.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/candidate/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="auth-box">
    <h1>Mi perfil</h1>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/candidate/profile-edit.php"
          class="auth-form" enctype="multipart/form-data" novalidate id="form-profile">

        <div class="form-group">
            <label for="full_name">Nombre completo *</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($user['full_name']); ?>">
        </div>

        <div class="form-group">
            <label>Correo electrónico</label>
            <!-- El email está desactivado (disabled) porque no se puede cambiar desde aquí -->
            <input type="email" value="<?= htmlspecialchars($user['email']); ?>" disabled
                   style="background:#f8fafc; color:#64748b;">
        </div>

        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="tel" id="phone" name="phone"
                   placeholder="Ej: 600 123 456"
                   value="<?= htmlspecialchars($profile['phone'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="city">Ciudad</label>
            <input type="text" id="city" name="city"
                   placeholder="Ej: Barcelona, Madrid..."
                   value="<?= htmlspecialchars($profile['city'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="profile_summary">Sobre mí</label>
            <textarea id="profile_summary" name="profile_summary" rows="5"
                      style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;"
                      placeholder="Cuéntanos sobre tu experiencia, habilidades y objetivos profesionales..."><?= htmlspecialchars($profile['profile_summary'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="cv">Currículum en PDF</label>
            <?php if (!empty($profile['cv_pdf_path'])): ?>
                <p style="margin-bottom:0.5rem; font-size:0.9rem; color:#374151;">
                    CV actual:
                    <a href="<?= BASE_URL . '/' . htmlspecialchars($profile['cv_pdf_path']); ?>"
                       target="_blank" class="btn-link">
                        📄 Ver CV
                    </a>
                </p>
            <?php endif; ?>
            <input type="file" id="cv" name="cv" accept=".pdf"
                   style="display:block; margin-top:4px;">
            <small style="color:#64748b; display:block; margin-top:4px;">
                Máximo 5 MB. Sube un nuevo PDF para reemplazar el actual.
            </small>
        </div>

        <button type="submit" class="btn-primary">Guardar cambios</button>
        <a href="<?= BASE_URL; ?>/candidate/index.php" class="btn-link btn-cancel">Cancelar</a>
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
            fieldError(fullName, 'El nombre completo es obligatorio.');

        const cv = document.getElementById('cv');
        if (cv.files.length > 0) {
            const file = cv.files[0];
            if (file.type !== 'application/pdf')
                fieldError(cv, 'Solo se aceptan archivos en formato PDF.');
            else if (file.size > 5 * 1024 * 1024)
                fieldError(cv, 'El archivo no puede superar los 5 MB.');
        }

        if (!valid) e.preventDefault();
    });
    </script>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
