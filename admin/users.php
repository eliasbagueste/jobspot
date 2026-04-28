<?php
// Carga la conexión a la base de datos y la configuración general
require_once __DIR__ . '/../config/database.php';

// Carga las funciones de autenticación
require_once __DIR__ . '/../includes/auth.php';

// Si no hay sesión → redirige al login
requireLogin();

// Si el usuario no es admin → error 403
requireRole('admin');

$pdo = getPDO();

// Activar / desactivar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $toggleId = (int) $_POST['toggle_id'];
    if ($toggleId !== (int) $_SESSION['user']['id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$toggleId]);
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$stmt = $pdo->query("SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/admin/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="card">
    <h1 style="margin-top:0;">Usuarios</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th style="text-align:center">Rol</th>
                <th style="text-align:center">Activo</th>
                <th style="text-align:center">Antigüedad</th>
                <th style="text-align:center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $u['id']); ?></td>
                    <td><?= htmlspecialchars($u['full_name']); ?></td>
                    <td><?= htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars($u['role']); ?>"><?= htmlspecialchars($u['role']); ?></span></td>
                    <td><?= $u['is_active'] ? 'Sí' : 'No'; ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['created_at']))); ?></td>
                    <td style="display:flex; gap:0.6rem; align-items:center; flex-wrap:wrap;">
                        <a href="<?= BASE_URL; ?>/admin/user-edit.php?id=<?= $u['id']; ?>" class="btn-edit">Editar</a>
                        <?php if ($u['id'] !== (int) $_SESSION['user']['id']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="toggle_id" value="<?= $u['id']; ?>">
                                <button type="submit" name="toggle_active"
                                        class="<?= $u['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                    <?= $u['is_active'] ? 'Desactivar' : 'Activar'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= BASE_URL; ?>/admin/user-delete.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar al usuario <?= htmlspecialchars(addslashes($u['full_name'])); ?>? Esta acción no se puede deshacer.');">
                            <input type="hidden" name="id" value="<?= $u['id']; ?>">
                            <button type="submit" class="btn-delete">Borrar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>