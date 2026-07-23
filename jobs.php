<?php
// jobs.php — Listado público de ofertas de empleo
// Cualquier visitante puede ver esta página aunque no tenga cuenta.

require_once __DIR__ . '/config/database.php';

$pdo = getPDO();

// Recogemos los filtros que el usuario puede enviar por la URL (formulario GET)
$search       = trim($_GET['search']   ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$modality     = trim($_GET['modality'] ?? '');
$contractType = trim($_GET['contract'] ?? '');

// Construyo la consulta con filtros dinámicos usando un array de condiciones.
// Así evito concatenar strings directamente en el SQL, que sería inseguro.
$conditions = ["j.status = 'published'"];
$params     = [];

if ($search !== '') {
    // Buscamos en el título y en la descripción de la oferta
    $conditions[] = "(j.title LIKE :search OR j.description LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if ($categorySlug !== '') {
    $conditions[] = "cat.slug = :category_slug";
    $params['category_slug'] = $categorySlug;
}

// Valido contra un array de valores permitidos para evitar valores inválidos
if (in_array($modality, ['onsite', 'hybrid', 'remote'])) {
    $conditions[] = "j.modality = :modality";
    $params['modality'] = $modality;
}

if (in_array($contractType, ['permanent', 'temporary', 'internship', 'freelance'])) {
    $conditions[] = "j.contract_type = :contract_type";
    $params['contract_type'] = $contractType;
}

// Unimos todas las condiciones con AND para formar el WHERE final
$where = implode(' AND ', $conditions);

// JOIN con companies y categories para traer el nombre de empresa y categoría en una sola consulta
$sql = "
    SELECT
        j.id,
        j.title,
        j.location,
        j.contract_type,
        j.workday,
        j.modality,
        j.salary_min,
        j.salary_max,
        j.currency,
        j.published_at,
        co.brand_name  AS company_name,
        co.logo_path   AS company_logo,
        cat.name       AS category_name
    FROM jobs j
    JOIN companies  co  ON co.id  = j.company_id
    JOIN categories cat ON cat.id = j.category_id
    WHERE $where
    ORDER BY j.published_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Categorías activas para el desplegable del formulario de filtros
$catStmt    = $pdo->query("SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $catStmt->fetchAll();

// Si el usuario es candidato, cargo sus favoritos para mostrar el corazón relleno
$favoriteIds = [];
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'candidate') {
    $favStmt = $pdo->prepare("SELECT job_id FROM favorite_jobs WHERE candidate_user_id = :uid");
    $favStmt->execute(['uid' => $_SESSION['user']['id']]);
    // fetchAll con FETCH_COLUMN devuelve un array simple de IDs, no un array de arrays
    $favoriteIds = $favStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Traduzco los valores ENUM de la BD a texto legible en español
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

require_once __DIR__ . '/includes/header.php';
?>

<section class="card">
    <h1 style="margin-top:0;">Job listings</h1>

    <!-- Formulario de filtros por GET para que la URL sea compartible y se pueda guardar o compartir -->
    <form method="get" action="<?= BASE_URL; ?>/jobs.php" class="filter-form">

        <div class="form-group">
            <input
                type="text"
                name="search"
                placeholder="Search jobs..."
                value="<?= htmlspecialchars($search); ?>"
            >
        </div>

        <div class="form-group">
            <select name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <!-- selected mantiene el filtro activo si el usuario ya había elegido esa categoría -->
                    <option
                        value="<?= htmlspecialchars($cat['slug']); ?>"
                        <?= $categorySlug === $cat['slug'] ? 'selected' : ''; ?>
                    >
                        <?= htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <select name="modality">
                <option value="">Any work mode</option>
                <?php foreach ($modalityLabels as $val => $label): ?>
                    <option value="<?= $val; ?>" <?= $modality === $val ? 'selected' : ''; ?>>
                        <?= $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <select name="contract">
                <option value="">Any contract type</option>
                <?php foreach ($contractLabels as $val => $label): ?>
                    <option value="<?= $val; ?>" <?= $contractType === $val ? 'selected' : ''; ?>>
                        <?= $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-primary">Filter</button>

        <!-- El enlace de limpiar solo aparece si hay algún filtro activo -->
        <?php if ($search !== '' || $categorySlug !== '' || $modality !== '' || $contractType !== ''): ?>
            <a href="<?= BASE_URL; ?>/jobs.php" class="btn-link">Clear filters</a>
        <?php endif; ?>
    </form>
</section>

<?php if (empty($jobs)): ?>
    <section class="card">
        <p>No jobs found matching the selected filters.</p>
    </section>
<?php else: ?>
    <!-- Mostramos cuántas ofertas ha encontrado la búsqueda -->
    <p style="margin-bottom: 1rem; color: #64748b;">
        <?= count($jobs); ?> job<?= count($jobs) !== 1 ? 's' : ''; ?> found
    </p>

    <?php foreach ($jobs as $job): ?>
        <div class="job-card">
            <div class="job-card-header">
                <div style="display:flex; align-items:center; gap:0.85rem;">

                    <!-- Avatar de la empresa: logo si tiene, o la primera letra del nombre si no -->
                    <div class="company-avatar">
                        <?php if (!empty($job['company_logo'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($job['company_logo']); ?>"
                                 alt="<?= htmlspecialchars($job['company_name']); ?>">
                        <?php else: ?>
                            <!-- Sin logo mostramos la primera letra del nombre en mayúscula -->
                            <!-- mb_ trabaja bien con caracteres especiales como tildes o ñ -->
                            <?= mb_strtoupper(mb_substr($job['company_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>

                    <div>
                        <!-- El título es un enlace a la página de detalle de la oferta -->
                        <h2 class="job-title">
                            <a href="<?= BASE_URL; ?>/job-detail.php?id=<?= $job['id']; ?>"
                               style="color:inherit; text-decoration:none;">
                                <?= htmlspecialchars($job['title']); ?>
                            </a>
                        </h2>
                        <p class="job-company"><?= htmlspecialchars($job['company_name']); ?></p>
                    </div>
                </div>
                <!-- Badge con la categoría de la oferta -->
                <span class="badge badge-candidate"><?= htmlspecialchars($job['category_name']); ?></span>
            </div>

            <!-- Metadatos de la oferta: ubicación, contrato, jornada, modalidad y salario -->
            <div class="job-meta">
                <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                <span>🕐 <?= $workdayLabels[$job['workday']] ?? $job['workday']; ?></span>
                <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>

                <!-- El salario es opcional: solo lo mostramos si la empresa lo indicó -->
                <?php if ($job['salary_min'] !== null && $job['salary_max'] !== null): ?>
                    <span>💶 <?= number_format((float)$job['salary_min'], 0, ',', '.'); ?>
                          – <?= number_format((float)$job['salary_max'], 0, ',', '.'); ?>
                          <?= htmlspecialchars($job['currency']); ?></span>
                <?php endif; ?>
                <span style="margin-left:auto; white-space:nowrap;">
                    Published: <?= date('d/m/Y', strtotime($job['published_at'])); ?>
                </span>
            </div>

            <div class="job-actions">
                <a href="<?= BASE_URL; ?>/job-detail.php?id=<?= $job['id']; ?>" class="btn-primary">
                    View job
                </a>

                <!-- El botón de favoritos solo aparece para candidatos con sesión iniciada -->
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'candidate'): ?>
                    <!--
                        data-job-id y data-favorited son atributos personalizados que usa el JS del footer
                        para saber qué oferta guardar y si ya está en favoritos o no.
                        El color y el icono cambian según el estado actual.
                    -->
                    <button class="btn-favorite" data-job-id="<?= $job['id']; ?>"
                            data-favorited="<?= in_array($job['id'], $favoriteIds) ? '1' : '0'; ?>"
                            title="<?= in_array($job['id'], $favoriteIds) ? 'Remove from favourites' : 'Add to favourites'; ?>"
                            style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:<?= in_array($job['id'], $favoriteIds) ? '#ef4444' : '#cbd5e1'; ?>; padding:0; line-height:1; margin-left:auto;">
                        <i class="<?= in_array($job['id'], $favoriteIds) ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
