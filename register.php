<?php
// register.php — Formulario de registro de nuevos usuarios (candidatos y empresas)
require_once __DIR__ . '/config/database.php';

// Si ya hay sesión activa, redirigimos al panel correspondiente
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin')         header('Location: ' . BASE_URL . '/admin/index.php');
    elseif ($role === 'candidate') header('Location: ' . BASE_URL . '/candidate/index.php');
    elseif ($role === 'company')   header('Location: ' . BASE_URL . '/company/index.php');
    else                           header('Location: ' . BASE_URL . '/index.php');
    exit;
}

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
        $error = 'All fields are required.';

    // Comprobamos que el email tenga formato válido
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'The email address is not valid.';

    // Comprobamos que la contraseña cumpla los requisitos de seguridad
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        // preg_match busca si hay alguna letra mayúscula (A-Z) en la contraseña
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        // preg_match busca si hay algún dígito (0-9) en la contraseña
        $error = 'Password must contain at least one number.';

    // Comprobamos que las dos contraseñas coincidan
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';

    // Comprobamos que el rol sea uno de los valores permitidos
    } elseif (!in_array($role, ['candidate', 'company'])) {
        $error = 'The account type is not valid.';

    } else {
        try {
            // Obtenemos la conexión a la base de datos
            $pdo = getPDO();

            // Comprobamos que el email no esté ya registrado
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $error = 'This email address is already registered.';
            } else {
                // Hasheamos la contraseña con bcrypt antes de guardarla
                // Nunca guardamos contraseñas en texto plano
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
            $error = 'Error registering user: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-box">
    <h1>Sign up</h1>
    <p>Enter your details to create an account.</p>

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
            <label for="full_name">Full name</label>
            <!-- value repopula el campo con lo que escribió el usuario si el formulario da error -->
        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($fullName); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label for="role">Account type</label>
            <select id="role" name="role" required>
                <option value="">Select an option</option>
                <option value="candidate" <?= $role === 'candidate' ? 'selected' : ''; ?>>Candidate</option>
                <option value="company"   <?= $role === 'company'   ? 'selected' : ''; ?>>Company</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn-primary">Sign up</button>
    </form>
</section>

<script>
// Validación en el cliente: mismo criterio que el PHP para dar feedback inmediato
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

    const fullName        = document.getElementById('full_name');
    const email           = document.getElementById('email');
    const role            = document.getElementById('role');
    const password        = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (!fullName.value.trim())
        error(fullName, 'Full name is required.');

    if (!email.value.trim()) {
        error(email, 'Email address is required.');
    } else if (!email.value.includes('@') || !email.value.includes('.')) {
        error(email, 'Enter a valid email address.');
    }

    if (!role.value)
        error(role, 'You must select an account type.');

    if (!password.value) {
        error(password, 'Password is required.');
    } else if (password.value.length < 8) {
        error(password, 'Password must be at least 8 characters long.');
    } else if (!/[A-Z]/.test(password.value)) {
        // /[A-Z]/ es una expresión regular que busca si existe alguna mayúscula
        error(password, 'Password must contain at least one uppercase letter.');
    } else if (!/[0-9]/.test(password.value)) {
        // /[0-9]/ busca si existe algún dígito numérico
        error(password, 'Password must contain at least one number.');
    }

    if (!confirmPassword.value) {
        error(confirmPassword, 'You must confirm your password.');
    } else if (password.value && confirmPassword.value !== password.value) {
        error(confirmPassword, 'Passwords do not match.');
    }

    if (!valid) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>