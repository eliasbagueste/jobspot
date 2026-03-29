<?php
require_once __DIR__ . '/config/database.php';

// Variable para guardar el mensaje de error y los valores del formulario. Empiezan vacías.
$error    = '';
$fullName = '';
$email    = '';
$role     = '';

// Si el formulario se envió por POST → procesa los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos los datos del formulario
    // ?? '' significa: si el campo no llega, guarda cadena vacía (alternativa a if isset)
    $fullName        = trim($_POST['full_name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $role            = $_POST['role'] ?? '';
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Comprobamos que ningún campo esté vacío
    if ($fullName === '' || $email === '' || $role === '' || $password === '' || $confirmPassword === '') {
        $error = 'Todos los campos son obligatorios.';

    // Comprobamos que el email tenga formato válido
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';

    // Comprobamos que las dos contraseñas coincidan
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';

    // Comprobamos que el rol sea uno de los valores permitidos
    } elseif (!in_array($role, ['candidate', 'company'])) {
        $error = 'El tipo de cuenta no es válido.';

    } else {
        try {
            // Obtenemos la conexión a la base de datos
            $pdo = getPDO();

            // Comprobamos que el email no esté ya registrado
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $error = 'Este correo electrónico ya está registrado.';
            } else {
                // Hasheamos la contraseña antes de guardarla
                $hash = password_hash($password, PASSWORD_BCRYPT);

                // Insertamos el nuevo usuario en la base de datos
                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, password_hash, role)
                    VALUES (:full_name, :email, :password_hash, :role)
                ");
                $stmt->execute([
                    'full_name'     => $fullName,
                    'email'         => $email,
                    'password_hash' => $hash,
                    'role'          => $role,
                ]);

                // Redirigimos al login con ?registered=1 en la URL para mostrar el mensaje de éxito
                header('Location: ' . BASE_URL . '/login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    }
}

//HTML DE LA PAGINA DE REGISTRO
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-box">
    <h1>Registrate</h1>
    <p>Ingresa tus datos para crear una cuenta.</p>

    <!-- Si $error no está vacío, muestra el mensaje de las validacios en rojo.
        htmlspecialchars convierte caracteres especiales en su versión segura,
        evita que se inyecte HTML malicioso  -->
    <?php if ($error !== ''): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL; ?>/register.php" method="post" class="auth-form" novalidate>
    
        <div class="form-group">
            <label for="full_name">Nombre completo</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($fullName); ?>" required>
        </div>
    
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label for="role">Tipo de cuenta</label>
            <select id="role" name="role" required>
                <option value="">Selecciona una opción</option>
                <option value="candidate" <?= $role === 'candidate' ? 'selected' : ''; ?>>Candidato</option>
                <option value="company"   <?= $role === 'company'   ? 'selected' : ''; ?>>Empresa</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn-primary">Registrate</button>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>