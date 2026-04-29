<?php
// admin/user-edit.php — Editar nombre, email y rol de un usuario
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

// Recogemos el ID del usuario a editar desde la URL (?id=X)
$id    = (int) ($_GET['id'] ?? 0);
$error = '';

if ($id === 0) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$pdo = getPDO();

// Procesamos el formulario si viene por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? '';

    if ($fullName === '' || $email === '' || $role === '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (!in_array($role, ['admin', 'candidate', 'company'])) {
        $error = 'El rol no es válido.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET full_name = :full_name, email = :email, role = :role
            WHERE id = :id
        ");
        $stmt->execute([
            'full_name' => $fullName,
            'email'     => $email,
            'role'      => $role,
            'id'        => $id,
        ]);

        header('Location: ' . BASE_URL . '/admin/users.php');
        exit;
    }
}

// Cargamos los datos actuales del usuario
$stmt = $pdo->prepare("SELECT id, full_name, email, role, updated_at FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();

// Si no existe el usuario, volvemos a la lista
if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-box">
    <h1>Editar usuario</h1>

<!-- Muestra el mensaje de error si algo ha fallado al guardar -->
    <?php if ($error !== ''): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL; ?>/admin/user-edit.php?id=<?= $user['id']; ?>" method="post" class="auth-form" novalidate>

        <div class="form-group">
            <label for="full_name">Nombre completo</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']); ?>">
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>">
        </div>

        <div class="form-group">
            <label for="role">Rol</label>
            <select id="role" name="role">
                <option value="admin"     <?= $user['role'] === 'admin'     ? 'selected' : ''; ?>>Admin</option>
                <option value="candidate" <?= $user['role'] === 'candidate' ? 'selected' : ''; ?>>Candidato</option>
                <option value="company"   <?= $user['role'] === 'company'   ? 'selected' : ''; ?>>Empresa</option>
            </select>
        </div>

        <button type="submit" class="btn-primary">Guardar cambios</button>
        <a href="<?= BASE_URL; ?>/admin/users.php" class="btn-link btn-cancel">Cancelar</a>
    </form>

    <script>
    // Validación en el cliente antes de enviar. El servidor también valida.
    document.querySelector('.auth-form').addEventListener('submit', function (e) {
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

        const fullName = document.getElementById('full_name');
        const email    = document.getElementById('email');
        const role     = document.getElementById('role');

        if (!fullName.value.trim())
            error(fullName, 'El nombre completo es obligatorio.');

        if (!email.value.trim()) {
            error(email, 'El correo electrónico es obligatorio.');
        } else if (!email.value.includes('@') || !email.value.includes('.')) {
            error(email, 'Introduce un correo electrónico válido.');
        }

        if (!role.value)
            error(role, 'Debes seleccionar un rol.');

        if (!valid) e.preventDefault();
    });
    </script>

    <!-- Muestra la fecha de última modificación del usuario -->
    <p style="margin-top: 20px; color: #64748B; font-size: 0.85rem;">
        Última modificación: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['updated_at']))); ?>
    </p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>