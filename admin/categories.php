<?php
// admin/categories.php — Gestión de categorías de ofertas de trabajo
// El admin puede ver, crear, activar/desactivar y eliminar categorías.
// Una categoría no puede eliminarse si tiene ofertas de trabajo asociadas.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Solo administradores pueden acceder a este panel
requireLogin();
requireRole('admin');

$user = $_SESSION['user'];
$pdo  = getPDO();

$error   = '';
$success = '';

// Convierte un nombre de categoría en un slug limpio para usarlo en URLs y filtros
// Ejemplo: "Tecnología & IT" → "tecnologia-it"
function makeSlug(string $name): string {
    // Tabla de sustitución: letras con tilde/acento → letra simple
    $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','à'=>'a','è'=>'e','ì'=>'i',
            'ò'=>'o','ù'=>'u','ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u','â'=>'a',
            'ê'=>'e','î'=>'i','ô'=>'o','û'=>'u','ñ'=>'n','ç'=>'c','ý'=>'y','ÿ'=>'y',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ä'=>'a','Ë'=>'e','Ï'=>'i',
            'Ö'=>'o','Ü'=>'u','Ñ'=>'n','Ç'=>'c'];
    $slug = mb_strtolower(strtr($name, $map), 'UTF-8');
    // Reemplazamos cualquier carácter que no sea letra o número por un guion
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

// Procesamos las acciones del formulario: crear, activar/desactivar o eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = trim($_POST['action'] ?? '');

    // ── Crear nueva categoría ──────────────────────────────
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $error = 'The category name is required.';
        } elseif (mb_strlen($name) > 100) {
            $error = 'The name cannot exceed 100 characters.';
        } else {
            // Comprobamos si ya existe una categoría con ese nombre (sin distinguir mayúsculas)
            $stmtCheck = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name)");
            $stmtCheck->execute(['name' => $name]);

            if ($stmtCheck->fetch()) {
                $error = 'A category with that name already exists.';
            } else {
                $slug = makeSlug($name);

                // La insertamos activa por defecto
                $stmtInsert = $pdo->prepare("
                    INSERT INTO categories (name, slug, is_active) VALUES (:name, :slug, 1)
                ");
                $stmtInsert->execute(['name' => $name, 'slug' => $slug]);
                $success = 'Category “' . htmlspecialchars($name) . '” created successfully.';
            }
        }
    }

    // ── Activar / desactivar categoría ────────────────────
    if ($action === 'toggle') {
        $catId = (int) ($_POST['category_id'] ?? 0);

        if ($catId > 0) {
            // Invertimos el valor actual de is_active (1→0 o 0→1)
            $stmtToggle = $pdo->prepare("
                UPDATE categories SET is_active = NOT is_active WHERE id = :id
            ");
            $stmtToggle->execute(['id' => $catId]);
            $success = 'Category status updated.';
        }
    }

    // ── Eliminar categoría ─────────────────────────────────
    if ($action === 'delete') {
        $catId = (int) ($_POST['category_id'] ?? 0);

        if ($catId > 0) {
            // Primero comprobamos cuántas ofertas usan esta categoría
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE category_id = :id");
            $stmtCount->execute(['id' => $catId]);
            $jobCount = (int) $stmtCount->fetchColumn();

            if ($jobCount > 0) {
                // No permitimos borrar si hay ofertas asociadas
                // Así protegemos la integridad referencial de la base de datos
                $error = "Cannot delete: this category has {$jobCount} job(s) associated with it.";
            } else {
                $stmtDelete = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                $stmtDelete->execute(['id' => $catId]);
                $success = 'Category deleted successfully.';
            }
        }
    }

    // Patrón POST-Redirect-GET: redirigimos para evitar reenvío del formulario
    // Codificamos el mensaje en la URL para mostrarlo tras la redirección
    if ($success !== '') {
        header('Location: ' . BASE_URL . '/admin/categories.php?ok=' . urlencode($success));
    } else {
        header('Location: ' . BASE_URL . '/admin/categories.php?err=' . urlencode($error));
    }
    exit;
}

// Recogemos los mensajes de éxito o error que vienen codificados en la URL
// (tras el patrón POST-Redirect-GET para evitar reenvíos del formulario)
if (isset($_GET['ok']))  $success = htmlspecialchars(urldecode($_GET['ok']));
if (isset($_GET['err'])) $error   = htmlspecialchars(urldecode($_GET['err']));

// Cargamos todas las categorías con el número de ofertas asociadas
// Usamos LEFT JOIN para incluir también las categorías sin ninguna oferta
$stmtCats = $pdo->query("
    SELECT
        c.id,
        c.name,
        c.is_active,
        COUNT(j.id) AS total_jobs
    FROM categories c
    LEFT JOIN jobs j ON j.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $stmtCats->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/admin/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to dashboard
</a>

<section class="card" style="padding:16px 24px;">
    <h1 style="margin:0 0 0.25rem;">Categories</h1>
    <p style="color:#64748b; margin:0;">Manage the categories available for job listings.</p>
</section>

<?php if ($success !== ''): ?>
    <div class="alert alert-success" style="margin-top:1rem;"><?= $success; ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error" style="margin-top:1rem;"><?= $error; ?></div>
<?php endif; ?>

<!-- ────────────────────────────────────────────────────── -->
<!-- Formulario para crear una nueva categoría             -->
<!-- ────────────────────────────────────────────────────── -->
<section class="card" style="padding:16px 24px;">
    <h2 style="margin:0 0 1rem;">New category</h2>
    <form method="post" action="<?= BASE_URL; ?>/admin/categories.php"
          id="form-category" novalidate
          style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
        <input type="hidden" name="action" value="create">
        <div class="form-group" style="margin:0; flex:1; min-width:200px;">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" maxlength="100"
                   placeholder="e.g. Technology, Hospitality..."
                   style="width:100%;">
        </div>
        <button type="submit" class="btn-primary">Add category</button>
    </form>

    <script>
    document.getElementById('form-category').addEventListener('submit', function (e) {
        document.querySelectorAll('.field-error').forEach(el => el.remove());
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        const name = document.getElementById('name');
        if (!name.value.trim()) {
            name.classList.add('input-error');
            const span = document.createElement('span');
            span.className = 'field-error';
            span.textContent = 'The category name is required.';
            name.closest('.form-group').appendChild(span);
            e.preventDefault();
        }
    });
    </script>
</section>

<!-- ────────────────────────────────────────────────────── -->
<!-- Listado de categorías existentes                       -->
<!-- ────────────────────────────────────────────────────── -->
<section class="card" style="margin-top:1rem;">
    <h2 style="margin-bottom:1rem;">
        Existing categories
        <small style="font-weight:normal; color:#64748b;">(<?= count($categories); ?> total)</small>
    </h2>

    <?php if (empty($categories)): ?>
        <p>No categories yet. Create the first one using the form above.</p>

    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.95rem;">
                <thead>
                    <tr style="border-bottom:2px solid #e5e7eb; text-align:left;">
                        <th style="padding:0.5rem 0.75rem;">Name</th>
                        <th style="padding:0.5rem 0.75rem; text-align:center;">Jobs</th>
                        <th style="padding:0.5rem 0.75rem; text-align:center;">Status</th>
                        <th style="padding:0.5rem 0.75rem; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:0.75rem;">
                                <?= htmlspecialchars($cat['name']); ?>
                            </td>
                            <td style="padding:0.75rem; text-align:center; color:#64748b;">
                                <?= $cat['total_jobs']; ?>
                            </td>
                            <td style="padding:0.75rem; text-align:center;">
                                <?php if ($cat['is_active']): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:0.75rem; text-align:right;">
                                <div style="display:flex; gap:0.5rem; justify-content:flex-end; flex-wrap:wrap;">

                                    <!-- Botón para activar o desactivar -->
                                    <form method="post" action="<?= BASE_URL; ?>/admin/categories.php" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="category_id" value="<?= $cat['id']; ?>">
                                        <button type="submit" class="btn-edit">
                                            <?= $cat['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>

                                    <!-- Botón para eliminar (deshabilitado si tiene ofertas) -->
                                    <?php if ($cat['total_jobs'] > 0): ?>
                                        <!-- Tooltip explicativo cuando no se puede borrar -->
                                        <button class="btn-delete" disabled
                                                title="Cannot delete: it has <?= $cat['total_jobs']; ?> job(s) associated with it"
                                                style="opacity:0.4; cursor:not-allowed;">
                                            Delete
                                        </button>
                                    <?php else: ?>
                                        <form method="post" action="<?= BASE_URL; ?>/admin/categories.php" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= $cat['id']; ?>">
                                            <button type="submit" class="btn-delete"
                                                    onclick="return confirm('Delete the category “<?= htmlspecialchars($cat['name']); ?>”?');">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
