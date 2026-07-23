<?php
// admin/users.php — Gestión de usuarios de la plataforma

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

$pdo = getPDO();

// Activar / desactivar usuario (toggle)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $toggleId = (int) ($_POST['toggle_id'] ?? 0);
    // Comprobamos que no sea el propio admin intentando desactivarse a sí mismo
    if ($toggleId > 0 && $toggleId !== (int) $_SESSION['user']['id']) {
        // NOT is_active invierte el valor: si era 1 pasa a 0 y viceversa
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$toggleId]);
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Cargamos todos los usuarios ordenados por ID ascendente
$stmt = $pdo->query("SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/admin/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Back to dashboard
</a>

<section class="card">
    <h1 style="margin-top:0;">Users</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th style="text-align:center">Role</th>
                <th style="text-align:center">Active</th>
                <th style="text-align:center">Joined</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $u['id']); ?></td>
                    <td><?= htmlspecialchars($u['full_name']); ?></td>
                    <td><?= htmlspecialchars($u['email']); ?></td>
                    <td style="text-align:center">
                        <!-- El nombre de clase incluye el rol para que CSS lo coloree distinto según el tipo de usuario -->
                        <span class="badge badge-<?= htmlspecialchars($u['role']); ?>">
                            <?= htmlspecialchars($u['role']); ?>
                        </span>
                    </td>
                    <td style="text-align:center">
                        <?php if ($u['is_active']): ?>
                            <span class="badge badge-active">Active</span>
                        <?php else: ?>
                            <span class="badge badge-rejected">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?= htmlspecialchars(date('d/m/Y', strtotime($u['created_at']))); ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:0.5rem; justify-content:flex-end; flex-wrap:wrap;">
                            <a href="<?= BASE_URL; ?>/admin/user-edit.php?id=<?= $u['id']; ?>"
                               class="btn-edit">Edit</a>

                            <?php
                            // No mostramos los botones de acción sobre la propia cuenta del admin
                            // para evitar que se auto-desactive o se borre a sí mismo
                            if ($u['id'] !== (int) $_SESSION['user']['id']): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="toggle_id" value="<?= $u['id']; ?>">
                                    <button type="submit" name="toggle_active"
                                            class="<?= $u['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <?= $u['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>

                                <form method="post" action="<?= BASE_URL; ?>/admin/user-delete.php"
                                      style="display:inline;"
                                      onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($u['full_name'])); ?>? This action cannot be undone.');">
                                      <!-- addslashes escapa las comillas del nombre para que no rompan el confirm() de JS -->
                                    <input type="hidden" name="id" value="<?= $u['id']; ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
