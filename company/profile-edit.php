<?php
// company/profile-edit.php — Editar perfil de empresa

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Cargamos el perfil de la empresa
$stmtCompany = $pdo->prepare("SELECT * FROM companies WHERE owner_user_id = :uid");
$stmtCompany->execute(['uid' => $user['id']]);
$company = $stmtCompany->fetch();

if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legalName   = trim($_POST['legal_name']  ?? '');
    $brandName   = trim($_POST['brand_name']  ?? '');
    $taxId       = trim($_POST['tax_id']      ?? '');
    $location    = trim($_POST['location']    ?? '');
    $website     = trim($_POST['website']     ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($legalName === '' || $brandName === '') {
        $error = 'El nombre legal y el nombre comercial son obligatorios.';
    } elseif ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = 'La URL del sitio web no es válida.';
    } else {
        // Mantenemos el logo actual por defecto
        $logoPath = $company['logo_path'];

        // Procesamos el logo si se ha subido uno nuevo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            // finfo detecta el tipo real del archivo desde su contenido, no solo por la extensión
            // Esto es más seguro que fiarse del nombre que manda el navegador
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['logo']['tmp_name']);

            if (!in_array($mimeType, $allowedTypes)) {
                $error = 'El logo debe ser una imagen (JPG, PNG, WebP o GIF).';
            } elseif ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
                $error = 'El logo no puede superar los 2 MB.';
            } else {
                $ext        = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $filename   = 'logo_' . $company['id'] . '_' . time() . '.' . $ext;
                $uploadsDir = __DIR__ . '/../uploads/logos/';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadsDir . $filename)) {
                    $logoPath = 'uploads/logos/' . $filename;
                } else {
                    $error = 'No se pudo guardar el logo. Comprueba los permisos de la carpeta uploads/logos/.';
                }
            }
        }

        if ($error === '') {
            $stmtUpdate = $pdo->prepare("
                UPDATE companies
                SET legal_name   = :legal_name,
                    brand_name   = :brand_name,
                    tax_id       = :tax_id,
                    location     = :location,
                    website      = :website,
                    description  = :description,
                    logo_path    = :logo_path
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'legal_name'  => $legalName,
                'brand_name'  => $brandName,
                'tax_id'      => $taxId !== '' ? $taxId : null,
                'location'    => $location !== '' ? $location : null,
                'website'     => $website !== '' ? $website : null,
                'description' => $description !== '' ? $description : null,
                'logo_path'   => $logoPath,
                'id'          => $company['id'],
            ]);

            // Recargamos los datos actualizados
            $stmtCompany->execute(['uid' => $user['id']]);
            $company = $stmtCompany->fetch();
            $success = 'Perfil de empresa actualizado correctamente.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/company/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="auth-box">
    <h1>Editar perfil de empresa</h1>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/company/profile-edit.php"
          class="auth-form" enctype="multipart/form-data" novalidate id="form-profile">

        <div class="form-group">
            <label for="legal_name">Nombre legal (razón social) *</label>
            <input type="text" id="legal_name" name="legal_name"
                   value="<?= htmlspecialchars($company['legal_name']); ?>">
        </div>

        <div class="form-group">
            <label for="brand_name">Nombre comercial *</label>
            <input type="text" id="brand_name" name="brand_name"
                   value="<?= htmlspecialchars($company['brand_name']); ?>">
        </div>

        <div class="form-group">
            <label for="tax_id">CIF / NIF</label>
            <input type="text" id="tax_id" name="tax_id"
                   placeholder="Ej: B12345678"
                   value="<?= htmlspecialchars($company['tax_id'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="location">Ubicación</label>
            <input type="text" id="location" name="location"
                   placeholder="Ej: Barcelona, Madrid..."
                   value="<?= htmlspecialchars($company['location'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="website">Sitio web</label>
            <input type="url" id="website" name="website"
                   placeholder="https://www.miempresa.com"
                   value="<?= htmlspecialchars($company['website'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="description">Descripción de la empresa</label>
            <textarea id="description" name="description" rows="5"
                      style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit;"
                      placeholder="Cuéntanos a qué se dedica tu empresa..."><?= htmlspecialchars($company['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="logo">Logo de la empresa</label>
            <?php if (!empty($company['logo_path'])): ?>
                <div style="margin-bottom:0.5rem;">
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($company['logo_path']); ?>"
                         alt="Logo actual"
                         style="max-height:80px; max-width:200px; border-radius:6px; border:1px solid #e5e7eb; padding:4px;">
                    <p style="font-size:0.82rem; color:#64748b; margin:0.25rem 0 0;">Logo actual. Sube uno nuevo para reemplazarlo.</p>
                </div>
            <?php endif; ?>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp,image/gif"
                   style="padding:0.3rem 0;">
            <span style="font-size:0.8rem; color:#94a3b8;">JPG, PNG, WebP o GIF · máx. 2 MB</span>
        </div>

        <button type="submit" class="btn-primary">Guardar cambios</button>
        <a href="<?= BASE_URL; ?>/company/index.php" class="btn-link btn-cancel">Cancelar</a>
    </form>

    <script>
    // Validación en el cliente: nombre legal, nombre comercial, URL y logo
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

        const legalName = document.getElementById('legal_name');
        const brandName = document.getElementById('brand_name');
        const website   = document.getElementById('website');
        const logo      = document.getElementById('logo');

        if (!legalName.value.trim())
            fieldError(legalName, 'El nombre legal es obligatorio.');

        if (!brandName.value.trim())
            fieldError(brandName, 'El nombre comercial es obligatorio.');

        if (website.value.trim() && !website.value.trim().startsWith('http'))
            fieldError(website, 'Introduce una URL válida (debe empezar por http:// o https://).');

        if (logo.files.length > 0) {
            const file = logo.files[0];
            const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!allowed.includes(file.type))
                fieldError(logo, 'El logo debe ser una imagen (JPG, PNG, WebP o GIF).');
            else if (file.size > 2 * 1024 * 1024)
                fieldError(logo, 'El logo no puede superar los 2 MB.');
        }

        if (!valid) e.preventDefault();
    });
    </script>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
