<?php
// Carga la conexión a la base de datos y la configuración general
require_once __DIR__ . '/../config/database.php';

// Carga las funciones de autenticación
require_once __DIR__ . '/../includes/auth.php';

// Si no hay sesión → redirige al login
requireLogin();

// Si el usuario no es admin → error 403
requireRole('admin');

// Obtenemos todos los usuarios de la base de datos
$pdo  = getPDO();
$stmt = $pdo->query("SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <h1>Usuarios</h1>

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
                    <td>
                        <a href="<?= BASE_URL; ?>/admin/user-edit.php?id=<?= $u['id']; ?>" class="btn-edit">Editar</a>
                        <a href="<?= BASE_URL; ?>/admin/user-delete.php?id=<?= $u['id']; ?>" class="btn-delete">Borrar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>