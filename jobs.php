<?php
// jobs.php — Listado público de ofertas de empleo
// Esta página es accesible para todos, incluidos visitantes sin sesión.

// Cargamos la conexión a la base de datos y la configuración general
require_once __DIR__ . '/config/database.php';

$pdo = getPDO();

// =========================================================
// FILTROS DE BÚSQUEDA
// =========================================================
// Recogemos los filtros que el usuario puede enviar por GET.
// trim() elimina espacios sobrantes. ?? '' evita errores si no llega el parámetro.
$search        = trim($_GET['search']   ?? '');
$categorySlug  = trim($_GET['category'] ?? '');
$modality      = trim($_GET['modality'] ?? '');
$contractType  = trim($_GET['contract'] ?? '');

// =========================================================
// CONSTRUCCIÓN DE LA CONSULTA CON FILTROS DINÁMICOS
// =========================================================
// Usamos un array de condiciones y otro de parámetros para
// construir la consulta de forma segura sin concatenar strings.
// Siempre filtramos por status = 'published' para mostrar solo
// las ofertas que el administrador ha aprobado.
$conditions = ["j.status = 'published'"];
$params     = [];

// Filtro por texto: busca en título y descripción de la oferta
if ($search !== '') {
    $conditions[] = "(j.title LIKE :search OR j.description LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

// Filtro por categoría usando el slug
if ($categorySlug !== '') {
    $conditions[] = "cat.slug = :category_slug";
    $params['category_slug'] = $categorySlug;
}

// Filtro por modalidad: presencial, híbrido o remoto
if (in_array($modality, ['onsite', 'hybrid', 'remote'])) {
    $conditions[] = "j.modality = :modality";
    $params['modality'] = $modality;
}

// Filtro por tipo de contrato: indefinido, temporal, prácticas, freelance
if (in_array($contractType, ['permanent', 'temporary', 'internship', 'freelance'])) {
    $conditions[] = "j.contract_type = :contract_type";
    $params['contract_type'] = $contractType;
}

// Unimos todas las condiciones con AND para construir el WHERE final
$where = implode(' AND ', $conditions);

// Consulta principal: trae las ofertas con el nombre de empresa y categoría
// JOIN con companies para mostrar la empresa y con categories para la categoría
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

// =========================================================
// CARGAMOS LAS CATEGORÍAS PARA EL DESPLEGABLE DE FILTRO
// =========================================================
$catStmt = $pdo->query("SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $catStmt->fetchAll();

// IDs de ofertas favoritas del candidato actual (para mostrar el corazón relleno)
$favoriteIds = [];
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'candidate') {
    $favStmt = $pdo->prepare("SELECT job_id FROM favorite_jobs WHERE candidate_user_id = :uid");
    $favStmt->execute(['uid' => $_SESSION['user']['id']]);
    $favoriteIds = $favStmt->fetchAll(PDO::FETCH_COLUMN);
}

// =========================================================
// ETIQUETAS LEGIBLES PARA LOS ENUMS DE LA BASE DE DATOS
// =========================================================
// Convertimos los valores internos del ENUM a textos en español
// para mostrarlos correctamente al usuario.
$modalityLabels = [
    'onsite' => 'Presencial',
    'hybrid' => 'Híbrido',
    'remote' => 'Remoto',
];

$contractLabels = [
    'permanent'   => 'Indefinido',
    'temporary'   => 'Temporal',
    'internship'  => 'Prácticas',
    'freelance'   => 'Freelance',
];

$workdayLabels = [
    'full_time' => 'Jornada completa',
    'part_time' => 'Media jornada',
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="card">
    <h1 style="margin-top:0;">Ofertas de empleo</h1>

    <!-- =====================================================
         FORMULARIO DE FILTROS
         Los filtros se envían por GET para que la URL sea
         compartible y el usuario pueda guardar la búsqueda.
    ====================================================== -->
    <form method="get" action="<?= BASE_URL; ?>/jobs.php" class="filter-form">

        <!-- Buscador de texto libre -->
        <div class="form-group">
            <input
                type="text"
                name="search"
                placeholder="Buscar oferta..."
                value="<?= htmlspecialchars($search); ?>"
            >
        </div>

        <!-- Desplegable de categorías -->
        <div class="form-group">
            <select name="category">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $cat): ?>
                    <option
                        value="<?= htmlspecialchars($cat['slug']); ?>"
                        <?= $categorySlug === $cat['slug'] ? 'selected' : ''; ?>
                    >
                        <?= htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Desplegable de modalidad -->
        <div class="form-group">
            <select name="modality">
                <option value="">Cualquier modalidad</option>
                <?php foreach ($modalityLabels as $val => $label): ?>
                    <option value="<?= $val; ?>" <?= $modality === $val ? 'selected' : ''; ?>>
                        <?= $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Desplegable de tipo de contrato -->
        <div class="form-group">
            <select name="contract">
                <option value="">Cualquier contrato</option>
                <?php foreach ($contractLabels as $val => $label): ?>
                    <option value="<?= $val; ?>" <?= $contractType === $val ? 'selected' : ''; ?>>
                        <?= $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-primary">Filtrar</button>

        <!-- Enlace para limpiar todos los filtros -->
        <?php if ($search !== '' || $categorySlug !== '' || $modality !== '' || $contractType !== ''): ?>
            <a href="<?= BASE_URL; ?>/jobs.php" class="btn-link">Limpiar filtros</a>
        <?php endif; ?>
    </form>
</section>

<!-- =========================================================
     LISTADO DE OFERTAS
     Si no hay resultados, mostramos un mensaje informativo.
========================================================= -->
<?php if (empty($jobs)): ?>
    <section class="card">
        <p>No se han encontrado ofertas con los filtros seleccionados.</p>
    </section>
<?php else: ?>
    <p style="margin-bottom: 1rem; color: #64748b;">
        <?= count($jobs); ?> oferta<?= count($jobs) !== 1 ? 's' : ''; ?> encontrada<?= count($jobs) !== 1 ? 's' : ''; ?>
    </p>

    <?php foreach ($jobs as $job): ?>
        <div class="job-card">
            <div class="job-card-header">
                <div style="display:flex; align-items:center; gap:0.85rem;">
                    <div class="company-avatar">
                        <?php if (!empty($job['company_logo'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($job['company_logo']); ?>"
                                 alt="<?= htmlspecialchars($job['company_name']); ?>">
                        <?php else: ?>
                            <?= mb_strtoupper(mb_substr($job['company_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="job-title">
                            <a href="<?= BASE_URL; ?>/job-detail.php?id=<?= $job['id']; ?>"
                               style="color:inherit; text-decoration:none;">
                                <?= htmlspecialchars($job['title']); ?>
                            </a>
                        </h2>
                        <p class="job-company"><?= htmlspecialchars($job['company_name']); ?></p>
                    </div>
                </div>
                <span class="badge badge-candidate"><?= htmlspecialchars($job['category_name']); ?></span>
            </div>

            <!-- Detalles de la oferta en etiquetas visuales -->
            <div class="job-meta">
                <span>📍 <?= htmlspecialchars($job['location']); ?></span>
                <span>📋 <?= $contractLabels[$job['contract_type']] ?? $job['contract_type']; ?></span>
                <span>🕐 <?= $workdayLabels[$job['workday']] ?? $job['workday']; ?></span>
                <span>💻 <?= $modalityLabels[$job['modality']] ?? $job['modality']; ?></span>

                <!-- Mostramos salario solo si está informado -->
                <?php if ($job['salary_min'] !== null && $job['salary_max'] !== null): ?>
                    <span>💶 <?= number_format((float)$job['salary_min'], 0, ',', '.'); ?>
                          – <?= number_format((float)$job['salary_max'], 0, ',', '.'); ?>
                          <?= htmlspecialchars($job['currency']); ?></span>
                <?php endif; ?>
                <span style="margin-left:auto; white-space:nowrap;">
                    Publicada: <?= date('d/m/Y', strtotime($job['published_at'])); ?>
                </span>
            </div>

            <div class="job-actions">
                <a href="<?= BASE_URL; ?>/job-detail.php?id=<?= $job['id']; ?>" class="btn-primary">
                    Ver oferta
                </a>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'candidate'): ?>
                    <button class="btn-favorite" data-job-id="<?= $job['id']; ?>"
                            data-favorited="<?= in_array($job['id'], $favoriteIds) ? '1' : '0'; ?>"
                            title="<?= in_array($job['id'], $favoriteIds) ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>"
                            style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:<?= in_array($job['id'], $favoriteIds) ? '#ef4444' : '#cbd5e1'; ?>; padding:0; line-height:1; margin-left:auto;">
                        <i class="<?= in_array($job['id'], $favoriteIds) ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
