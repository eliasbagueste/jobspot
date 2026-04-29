<?php
// login.php — Formulario de inicio de sesión

// database.php también carga config.php, que inicia la sesión PHP
require_once __DIR__ . '/config/database.php';

// Si el usuario ya tiene sesión activa, redirige según su rol
// (no tiene sentido volver a mostrar el login)
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/index.php');
    } elseif ($_SESSION['user']['role'] === 'candidate') {
        header('Location: ' . BASE_URL . '/candidate/index.php');
    } elseif ($_SESSION['user']['role'] === 'company') {
        header('Location: ' . BASE_URL . '/company/index.php');
    } else {
        header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
}
// Variables para el mensaje de error y para repopular el email si el login falla
$error = '';
$email = '';

// Solo procesamos el formulario cuando llega por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Debes introducir tu correo electrónico y tu contraseña.';
    } else {
        try {
            $pdo = getPDO();

            // Buscamos el usuario por email (LIMIT 1 porque el email es único)
            $stmt = $pdo->prepare("
                SELECT id, full_name, email, password_hash, role, is_active
                FROM users
                WHERE email = :email
                LIMIT 1
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                // No decimos "el email no existe" para no dar pistas a posibles atacantes
                $error = 'El correo electrónico o la contraseña no son correctos.';
            } elseif (!(bool) $user['is_active']) {
                $error = 'Tu cuenta está desactivada.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                // password_verify compara la contraseña con el hash almacenado en la BD
                $error = 'El correo electrónico o la contraseña no son correctos.';
            } else {
                // Regeneramos el ID de sesión por seguridad al hacer login
                // Esto evita ataques de "session fixation" (fijación de sesión)
                session_regenerate_id(true);

                // Guardamos en sesión solo lo imprescindible (nunca el hash de contraseña)
                $_SESSION['user'] = [
                    'id'        => (int) $user['id'],
                    'full_name' => $user['full_name'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                ];

                // Redirigimos al panel que corresponde según el rol del usuario
                if ($_SESSION['user']['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/index.php');
                } elseif ($_SESSION['user']['role'] === 'candidate') {
                    header('Location: ' . BASE_URL . '/candidate/index.php');
                } elseif ($_SESSION['user']['role'] === 'company') {
                    header('Location: ' . BASE_URL . '/company/index.php');
                } else {
                    header('Location: ' . BASE_URL . '/index.php');
                }
                exit;
            }
        } catch (PDOException $e) {
            // En producción ocultamos el mensaje técnico; en local lo mostramos para depurar
            if (APP_ENV === 'prod') {
                $error = 'Se ha producido un error al iniciar sesión.';
            } else {
                $error = 'Error al iniciar sesión: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-box">
    <h1>Iniciar sesión</h1>
    <p>Accede a JobSpot con tu correo electrónico y contraseña.</p>

    <!-- Si viene de registrarse, muestra mensaje de éxito -->
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">
            Gracias por registrarte. Ya puedes iniciar sesión.
        </div>
    <?php endif; ?>

    <!-- Si $error no está vacío, muestra el mensaje. htmlspecialchars evita que se inyecte HTML malicioso -->
    <?php if ($error !== ''): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL; ?>/login.php" method="post" class="auth-form" novalidate>
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email); ?>"
            >
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
            >
        </div>

        <button type="submit" class="btn-primary">Entrar</button>

        <!-- Enlace de recuperación de contraseña: funcionalidad pendiente por tiempo -->
        <p style="margin-top:1rem; text-align:center; font-size:0.9rem;">
            <a href="#" id="forgot-link" style="color:#6366f1; text-decoration:none;">
                ¿Olvidaste tu contraseña?
            </a>
        </p>
    </form>

    <!-- Modal informativo: se muestra al pulsar el enlace de recuperación -->
    <div id="forgot-modal"
         style="display:none; margin-top:1rem; padding:1rem 1.25rem;
                background:#f0f4ff; border:1px solid #c7d2fe; border-radius:8px;
                color:#3730a3; font-size:0.9rem; line-height:1.5;">
        <strong>Recuperación de contraseña</strong><br>
        Esta funcionalidad quedó pendiente de implementar por limitaciones de tiempo.
        Si has olvidado tu contraseña, contacta con el administrador de la plataforma.
    </div>
</section>

<script>
// Validación en el cliente para mostrar errores sin necesitar recargar la página
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

    const email    = document.getElementById('email');
    const password = document.getElementById('password');

    if (!email.value.trim())
        error(email, 'El correo electrónico es obligatorio.');

    if (!password.value)
        error(password, 'La contraseña es obligatoria.');

    if (!valid) e.preventDefault();
});

// Al pulsar "¿Olvidaste tu contraseña?" muestra u oculta el aviso informativo
document.getElementById('forgot-link').addEventListener('click', function (e) {
    e.preventDefault(); // evita que el enlace # haga scroll al inicio de la página
    const modal = document.getElementById('forgot-modal');
    modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>