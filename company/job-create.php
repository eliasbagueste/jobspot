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
        $error = 'El título de la oferta es obligatorio.';
    } elseif ($description === '') {
        $error = 'La descripción es obligatoria.';
    } elseif ($location === '') {
        $error = 'La ubicación es obligatoria.';
    } elseif ($categoryId === 0) {
        $error = 'Debes seleccionar una categoría.';
    } elseif (!in_array($contractType, $validContracts)) {
        $error = 'El tipo de contrato no es válido.';
    } elseif (!in_array($workday, $validWorkdays)) {
        $error = 'La jornada no es válida.';
    } elseif (!in_array($modality, $validModalities)) {
        $error = 'La modalidad no es válida.';
    } else {
        // Convertimos salario a null si no se informó, o a float si se informó
        $salaryMinVal = ($salaryMin !== '') ? (float) $salaryMin : null;
        $salaryMaxVal = ($salaryMax !== '') ? (float) $salaryMax : null;

        // Comprobamos que el salario mínimo no supere al máximo
        if ($salaryMinVal !== null && $salaryMaxVal !== null && $salaryMinVal > $salaryMaxVal) {
            $error = 'El salario mínimo no puede ser mayor que el salario máximo.';
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
    ← Volver a mis ofertas
</a>

<section class="auth-box">
    <h1>Crear nueva oferta</h1>
    <p>La oferta se publicará de forma inmediata y será visible para los candidatos.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL; ?>/company/job-create.php" class="auth-form" novalidate id="form-job">

        <!-- Título de la oferta -->
        <div class="form-group">
            <label for="title">Título del puesto *</label>
            <input type="text" id="title" name="title"
                   value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>"
                   placeholder="Ej: Desarrollador PHP Junior" required>
        </div>

        <!-- Descripción del puesto -->
        <div class="form-group">
            <label for="description">Descripción del puesto *</label>
            <textarea id="description" name="description" rows="6" required
                      style="width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; font-size:0.95rem;"
                      placeholder="Describe las funciones, requisitos y lo que ofreces..."
            ><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <!-- Ubicación -->
        <div class="form-group">
            <label for="location">Ubicación *</label>
            <input type="text" id="location" name="location"
                   value="<?= htmlspecialchars($_POST['location'] ?? ''); ?>"
                   placeholder="Ej: Barcelona, Madrid, Remoto..." required>
        </div>

        <!-- Categoría -->
        <div class="form-group">
            <label for="category_id">Categoría *</label>
            <select id="category_id" name="category_id" required>
                <option value="0">Selecciona una categoría</option>
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
            <label for="contract_type">Tipo de contrato *</label>
            <select id="contract_type" name="contract_type" required>
                <option value="">Selecciona...</option>
                <option value="permanent"  <?= ($_POST['contract_type'] ?? '') === 'permanent'  ? 'selected' : ''; ?>>Indefinido</option>
                <option value="temporary"  <?= ($_POST['contract_type'] ?? '') === 'temporary'  ? 'selected' : ''; ?>>Temporal</option>
                <option value="internship" <?= ($_POST['contract_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Prácticas</option>
                <option value="freelance"  <?= ($_POST['contract_type'] ?? '') === 'freelance'  ? 'selected' : ''; ?>>Freelance</option>
            </select>
        </div>

        <!-- Jornada -->
        <div class="form-group">
            <label for="workday">Jornada *</label>
            <select id="workday" name="workday" required>
                <option value="">Selecciona...</option>
                <option value="full_time" <?= ($_POST['workday'] ?? '') === 'full_time' ? 'selected' : ''; ?>>Jornada completa</option>
                <option value="part_time" <?= ($_POST['workday'] ?? '') === 'part_time' ? 'selected' : ''; ?>>Media jornada</option>
            </select>
        </div>

        <!-- Modalidad -->
        <div class="form-group">
            <label for="modality">Modalidad *</label>
            <select id="modality" name="modality" required>
                <option value="">Selecciona...</option>
                <option value="onsite" <?= ($_POST['modality'] ?? '') === 'onsite' ? 'selected' : ''; ?>>Presencial</option>
                <option value="hybrid" <?= ($_POST['modality'] ?? '') === 'hybrid' ? 'selected' : ''; ?>>Híbrido</option>
                <option value="remote" <?= ($_POST['modality'] ?? '') === 'remote' ? 'selected' : ''; ?>>Remoto</option>
            </select>
        </div>

        <!-- Salario (opcional) -->
        <div class="form-group">
            <label>Salario anual bruto (opcional)</label>
            <div style="display:flex; gap:1rem;">
                <input type="number" id="salary_min" name="salary_min" min="0" step="1"
                       placeholder="Mínimo (€)"
                       value="<?= htmlspecialchars($_POST['salary_min'] ?? ''); ?>"
                       style="flex:1; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px;">
                <input type="number" id="salary_max" name="salary_max" min="0" step="1"
                       placeholder="Máximo (€)"
                       value="<?= htmlspecialchars($_POST['salary_max'] ?? ''); ?>"
                       style="flex:1; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px;">
            </div>
        </div>

        <button type="submit" class="btn-primary">Publicar oferta</button>
        <a href="<?= BASE_URL; ?>/company/jobs.php" class="btn-link btn-cancel">Cancelar</a>
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
        error(title, 'El título del puesto es obligatorio.');

    if (!desc.value.trim())
        error(desc, 'La descripción es obligatoria.');

    if (!location.value.trim())
        error(location, 'La ubicación es obligatoria.');

    if (category.value === '0')
        error(category, 'Debes seleccionar una categoría.');

    if (!contract.value)
        error(contract, 'Debes seleccionar un tipo de contrato.');

    if (!workday.value)
        error(workday, 'Debes seleccionar una jornada.');

    if (!modality.value)
        error(modality, 'Debes seleccionar una modalidad.');

    const minVal = salaryMin.value !== '' ? parseFloat(salaryMin.value) : null;
    const maxVal = salaryMax.value !== '' ? parseFloat(salaryMax.value) : null;

    if (minVal !== null && minVal < 0)
        error(salaryMin, 'El salario mínimo no puede ser negativo.');

    if (maxVal !== null && maxVal < 0)
        error(salaryMax, 'El salario máximo no puede ser negativo.');

    if (minVal !== null && maxVal !== null && minVal > maxVal)
        error(salaryMin, 'El salario mínimo no puede ser mayor que el máximo.');

    if (!valid) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
