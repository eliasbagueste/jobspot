<?php
// company/job-edit.php — Editar una oferta de trabajo existente

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Obtenemos el perfil de empresa del usuario actual
$stmtCompany = $pdo->prepare("SELECT * FROM companies WHERE owner_user_id = :uid");
$stmtCompany->execute(['uid' => $user['id']]);
$company = $stmtCompany->fetch();

if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// Cargamos la oferta a editar verificando que pertenece a ESTA empresa
// Si no lo comprobamos, cualquier empresa podría editar ofertas ajenas
$jobId = (int) ($_GET['id'] ?? 0);

if ($jobId === 0) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

$stmtJob = $pdo->prepare("
    SELECT * FROM jobs WHERE id = :id AND company_id = :cid
");
$stmtJob->execute(['id' => $jobId, 'cid' => $company['id']]);
$job = $stmtJob->fetch();

// Si la oferta no existe o no pertenece a esta empresa, redirigimos
if (!$job) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

// No se puede editar una oferta cerrada o rechazada
if (in_array($job['status'], ['closed', 'rejected'])) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

// Cargamos las categorías para el desplegable
$stmtCats = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $stmtCats->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title        = trim($_POST['title']         ?? '');
    $description  = trim($_POST['description']   ?? '');
    $location     = trim($_POST['location']      ?? '');
    $categoryId   = (int) ($_POST['category_id'] ?? 0);
    $contractType = trim($_POST['contract_type'] ?? '');
    $workday      = trim($_POST['workday']        ?? '');
    $modality     = trim($_POST['modality']      ?? '');
    $salaryMin    = trim($_POST['salary_min']     ?? '');
    $salaryMax    = trim($_POST['salary_max']     ?? '');

    $validContracts  = ['permanent', 'temporary', 'internship', 'freelance'];
    $validWorkdays   = ['full_time', 'part_time'];
    $validModalities = ['onsite', 'hybrid', 'remote'];

    if ($title === '') {
        $error = 'The title is required.';
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
        $salaryMinVal = ($salaryMin !== '') ? (float) $salaryMin : null;
        $salaryMaxVal = ($salaryMax !== '') ? (float) $salaryMax : null;

        if ($salaryMinVal !== null && $salaryMaxVal !== null && $salaryMinVal > $salaryMaxVal) {
            $error = 'The minimum salary cannot be greater than the maximum salary.';
        } else {
            // Al editar mantenemos el estado actual de la oferta
            $newStatus = $job['status'];

            $stmtUpdate = $pdo->prepare("
                UPDATE jobs SET
                    category_id   = :category_id,
                    title         = :title,
                    description   = :description,
                    location      = :location,
                    contract_type = :contract_type,
                    workday       = :workday,
                    modality      = :modality,
                    salary_min    = :salary_min,
                    salary_max    = :salary_max,
                    status        = :status
                WHERE id = :id AND company_id = :cid
            ");
            $stmtUpdate->execute([
                'category_id'   => $categoryId,
                'title'         => $title,
                'description'   => $description,
                'location'      => $location,
                'contract_type' => $contractType,
                'workday'       => $workday,
                'modality'      => $modality,
                'salary_min'    => $salaryMinVal,
                'salary_max'    => $salaryMaxVal,
                'status'        => $newStatus,
                'id'            => $jobId,
                'cid'           => $company['id'],
            ]);

            header('Location: ' . BASE_URL . '/company/jobs.php?edited=1');
            exit;
        }
    }
}

// Si viene de GET, precargamos los valores actuales de la oferta en el formulario
$formData = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $job;

require_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-box">
    <h1>Edit job listing</h1>
    <p>Changes are saved while keeping the current status of the job listing.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/company/job-edit.php?id=<?= $jobId; ?>" class="auth-form" novalidate id="form-job">

        <div class="form-group">
            <label for="title">Job title *</label>
            <input type="text" id="title" name="title"
                   value="<?= htmlspecialchars($formData['title'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="6" required
                      style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; font-size:0.95rem;"
            ><?= htmlspecialchars($formData['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="location">Location *</label>
            <input type="text" id="location" name="location"
                   value="<?= htmlspecialchars($formData['location'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="0">Select a category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id']; ?>"
                        <?= (int)($formData['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="contract_type">Contract type *</label>
            <select id="contract_type" name="contract_type" required>
                <option value="">Select...</option>
                <option value="permanent"  <?= ($formData['contract_type'] ?? '') === 'permanent'  ? 'selected' : ''; ?>>Permanent</option>
                <option value="temporary"  <?= ($formData['contract_type'] ?? '') === 'temporary'  ? 'selected' : ''; ?>>Temporary</option>
                <option value="internship" <?= ($formData['contract_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                <option value="freelance"  <?= ($formData['contract_type'] ?? '') === 'freelance'  ? 'selected' : ''; ?>>Freelance</option>
            </select>
        </div>

        <div class="form-group">
            <label for="workday">Working hours *</label>
            <select id="workday" name="workday" required>
                <option value="">Select...</option>
                <option value="full_time" <?= ($formData['workday'] ?? '') === 'full_time' ? 'selected' : ''; ?>>Full-time</option>
                <option value="part_time" <?= ($formData['workday'] ?? '') === 'part_time' ? 'selected' : ''; ?>>Part-time</option>
            </select>
        </div>

        <div class="form-group">
            <label for="modality">Work mode *</label>
            <select id="modality" name="modality" required>
                <option value="">Select...</option>
                <option value="onsite" <?= ($formData['modality'] ?? '') === 'onsite' ? 'selected' : ''; ?>>On-site</option>
                <option value="hybrid" <?= ($formData['modality'] ?? '') === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                <option value="remote" <?= ($formData['modality'] ?? '') === 'remote' ? 'selected' : ''; ?>>Remote</option>
            </select>
        </div>

        <div class="form-group">
            <label>Gross annual salary (optional)</label>
            <div style="display:flex; gap:1rem;">
                <input type="number" id="salary_min" name="salary_min" min="0" step="1"
                       placeholder="Minimum (€)"
                       value="<?= htmlspecialchars($formData['salary_min'] ?? ''); ?>"
                       style="flex:1; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px;">
                <input type="number" id="salary_max" name="salary_max" min="0" step="1"
                       placeholder="Maximum (€)"
                       value="<?= htmlspecialchars($formData['salary_max'] ?? ''); ?>"
                       style="flex:1; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px;">
            </div>
        </div>

        <button type="submit" class="btn-primary">Save changes</button>
        <a href="<?= BASE_URL; ?>/company/jobs.php" class="btn-link btn-cancel">Cancel</a>
    </form>
</section>

<script>
// Validación en el cliente antes de enviar. El servidor también valida por si el JS está desactivado.
document.getElementById('form-job').addEventListener('submit', function (e) {
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

    const title     = document.getElementById('title');
    const desc      = document.getElementById('description');
    const location  = document.getElementById('location');
    const category  = document.getElementById('category_id');
    const contract  = document.getElementById('contract_type');
    const workday   = document.getElementById('workday');
    const modality  = document.getElementById('modality');
    const salaryMin = document.getElementById('salary_min');
    const salaryMax = document.getElementById('salary_max');

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
