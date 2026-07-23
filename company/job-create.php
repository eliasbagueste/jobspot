<?php
// company/job-create.php — Formulario para crear una nueva oferta de trabajo

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Verificamos que la empresa tiene perfil y está verificada
$stmtCompany = $pdo->prepare("SELECT * FROM companies WHERE owner_user_id = :uid");
$stmtCompany->execute(['uid' => $user['id']]);
$company = $stmtCompany->fetch();

// Sin perfil → volver al panel
if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// Sin verificación → no puede publicar
if (!(bool)$company['is_verified']) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// Cargamos las categorías disponibles para el desplegable
$stmtCats = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $stmtCats->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos y limpiamos todos los campos del formulario
    $title        = trim($_POST['title']         ?? '');
    $description  = trim($_POST['description']   ?? '');
    $location     = trim($_POST['location']      ?? '');
    $categoryId   = (int) ($_POST['category_id'] ?? 0);
    $contractType = trim($_POST['contract_type'] ?? '');
    $workday      = trim($_POST['workday']        ?? '');
    $modality     = trim($_POST['modality']      ?? '');
    $salaryMin    = trim($_POST['salary_min']     ?? '');
    $salaryMax    = trim($_POST['salary_max']     ?? '');

    // Valores válidos para los campos ENUM de la base de datos
    $validContracts  = ['permanent', 'temporary', 'internship', 'freelance'];
    $validWorkdays   = ['full_time', 'part_time'];
    $validModalities = ['onsite', 'hybrid', 'remote'];

    // Validaciones obligatorias
    if ($title === '') {
        $error = 'The job title is required.';
    } elseif ($description === '') {
        $error = 'The description is required.';
    } elseif ($location === '') {
        $error = 'The location is required.';
    } elseif ($categoryId === 0) {
        $error = 'You must select a category.';
    } elseif (!in_array($contractType, $validContracts)) {
        $error = 'The contract type is not valid.';
    } elseif (!in_array($workday, $validWorkdays)) {
        $error = 'The working hours are not valid.';
    } elseif (!in_array($modality, $validModalities)) {
        $error = 'The work mode is not valid.';
    } else {
        // Convertimos salario a null si no se informó, o a float si se informó
        $salaryMinVal = ($salaryMin !== '') ? (float) $salaryMin : null;
        $salaryMaxVal = ($salaryMax !== '') ? (float) $salaryMax : null;

        // Comprobamos que el salario mínimo no supere al máximo
        if ($salaryMinVal !== null && $salaryMaxVal !== null && $salaryMinVal > $salaryMaxVal) {
            $error = 'The minimum salary cannot be greater than the maximum salary.';
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO jobs
                    (company_id, category_id, title, description, location,
                     contract_type, workday, modality, salary_min, salary_max, status, published_at)
                VALUES
                    (:company_id, :category_id, :title, :description, :location,
                     :contract_type, :workday, :modality, :salary_min, :salary_max, 'published', NOW())
            ");
            $stmtInsert->execute([
                'company_id'    => $company['id'],
                'category_id'   => $categoryId,
                'title'         => $title,
                'description'   => $description,
                'location'      => $location,
                'contract_type' => $contractType,
                'workday'       => $workday,
                'modality'      => $modality,
                'salary_min'    => $salaryMinVal,
                'salary_max'    => $salaryMaxVal,
            ]);

            // Redirigimos al listado de ofertas con mensaje de éxito en la URL
            header('Location: ' . BASE_URL . '/company/jobs.php?created=1');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/company/jobs.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to my jobs
</a>

<section class="auth-box">
    <h1>Create new job listing</h1>
    <p>The job listing will be published immediately and visible to candidates.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/company/job-create.php" class="auth-form" novalidate id="form-job">

        <!-- Título de la oferta -->
        <div class="form-group">
            <label for="title">Job title *</label>
            <input type="text" id="title" name="title"
                   value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>"
                   placeholder="e.g. Junior PHP Developer" required>
        </div>

        <!-- Descripción del puesto -->
        <div class="form-group">
            <label for="description">Job description *</label>
            <textarea id="description" name="description" rows="6" required
                      style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; font-size:0.95rem;"
                      placeholder="Describe the responsibilities, requirements and what you offer..."
            ><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <!-- Ubicación -->
        <div class="form-group">
            <label for="location">Location *</label>
            <input type="text" id="location" name="location"
                   value="<?= htmlspecialchars($_POST['location'] ?? ''); ?>"
                   placeholder="e.g. Dublin, Cork, Remote..." required>
        </div>

        <!-- Categoría -->
        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="0">Select a category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id']; ?>"
                        <?= ((int)($_POST['category_id'] ?? 0)) === (int)$cat['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tipo de contrato -->
        <div class="form-group">
            <label for="contract_type">Contract type *</label>
            <select id="contract_type" name="contract_type" required>
                <option value="">Select...</option>
                <option value="permanent"  <?= ($_POST['contract_type'] ?? '') === 'permanent'  ? 'selected' : ''; ?>>Permanent</option>
                <option value="temporary"  <?= ($_POST['contract_type'] ?? '') === 'temporary'  ? 'selected' : ''; ?>>Temporary</option>
                <option value="internship" <?= ($_POST['contract_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                <option value="freelance"  <?= ($_POST['contract_type'] ?? '') === 'freelance'  ? 'selected' : ''; ?>>Freelance</option>
            </select>
        </div>

        <!-- Jornada -->
        <div class="form-group">
            <label for="workday">Working hours *</label>
            <select id="workday" name="workday" required>
                <option value="">Select...</option>
                <option value="full_time" <?= ($_POST['workday'] ?? '') === 'full_time' ? 'selected' : ''; ?>>Full-time</option>
                <option value="part_time" <?= ($_POST['workday'] ?? '') === 'part_time' ? 'selected' : ''; ?>>Part-time</option>
            </select>
        </div>

        <!-- Modalidad -->
        <div class="form-group">
            <label for="modality">Work mode *</label>
            <select id="modality" name="modality" required>
                <option value="">Select...</option>
                <option value="onsite" <?= ($_POST['modality'] ?? '') === 'onsite' ? 'selected' : ''; ?>>On-site</option>
                <option value="hybrid" <?= ($_POST['modality'] ?? '') === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                <option value="remote" <?= ($_POST['modality'] ?? '') === 'remote' ? 'selected' : ''; ?>>Remote</option>
            </select>
        </div>

        <!-- Salario (opcional) -->
        <div class="form-group">
            <label>Gross annual salary (optional)</label>
            <div style="display:flex; gap:1rem;">
                <input type="number" id="salary_min" name="salary_min" min="0" step="1"
                       placeholder="Minimum (€)"
                       value="<?= htmlspecialchars($_POST['salary_min'] ?? ''); ?>"
                       style="flex:1; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px;">
                <input type="number" id="salary_max" name="salary_max" min="0" step="1"
                       placeholder="Maximum (€)"
                       value="<?= htmlspecialchars($_POST['salary_max'] ?? ''); ?>"
                       style="flex:1; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px;">
            </div>
        </div>

        <button type="submit" class="btn-primary">Publish job listing</button>
        <a href="<?= BASE_URL; ?>/company/jobs.php" class="btn-link btn-cancel">Cancel</a>
    </form>
</section>

<script>
// Validación en el cliente antes de enviar. El servidor también valida por si el JS está desactivado.
document.getElementById('form-job').addEventListener('submit', function (e) {
    // Limpiamos errores anteriores
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

    const title      = document.getElementById('title');
    const desc       = document.getElementById('description');
    const location   = document.getElementById('location');
    const category   = document.getElementById('category_id');
    const contract   = document.getElementById('contract_type');
    const workday    = document.getElementById('workday');
    const modality   = document.getElementById('modality');
    const salaryMin  = document.getElementById('salary_min');
    const salaryMax  = document.getElementById('salary_max');

    if (!title.value.trim())
        error(title, 'The job title is required.');

    if (!desc.value.trim())
        error(desc, 'The description is required.');

    if (!location.value.trim())
        error(location, 'The location is required.');

    if (category.value === '0')
        error(category, 'You must select a category.');

    if (!contract.value)
        error(contract, 'You must select a contract type.');

    if (!workday.value)
        error(workday, 'You must select working hours.');

    if (!modality.value)
        error(modality, 'You must select a work mode.');

    const minVal = salaryMin.value !== '' ? parseFloat(salaryMin.value) : null;
    const maxVal = salaryMax.value !== '' ? parseFloat(salaryMax.value) : null;

    if (minVal !== null && minVal < 0)
        error(salaryMin, 'The minimum salary cannot be negative.');

    if (maxVal !== null && maxVal < 0)
        error(salaryMax, 'The maximum salary cannot be negative.');

    if (minVal !== null && maxVal !== null && minVal > maxVal)
        error(salaryMin, 'The minimum salary cannot be greater than the maximum.');

    if (!valid) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
