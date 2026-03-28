<?php
// login.php

/**
 * Carga la conexión a base de datos.
 * database.php a su vez carga config.php,
 * que inicia sesión y carga la configuración general.
 */
require_once __DIR__ . '/config/database.php';

/**
 * Si el usuario ya ha iniciado sesión,
 * no tiene sentido volver a mostrarle el login.
 * Lo redirigimos directamente al panel de prueba.
 */
if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

/**
 * Variables para controlar errores y valores del formulario.
 * Así podemos volver a mostrar el email si falla el login.
 */
$error = '';
$email = '';

/**
 * Procesa el formulario solo cuando llega por POST.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Recoge y limpia los datos enviados por el formulario.
     */
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /**
     * Validación básica.
     * Si falta algún campo, mostramos error.
     */
    if ($email === '' || $password === '') {
        $error = 'Debes introducir tu correo electrónico y tu contraseña.';
    } else {
        try {
            /**
             * Obtiene la conexión PDO.
             */
            $pdo = getPDO();

            /**
             * Busca al usuario por email.
             * Solo necesitamos un registro, por eso LIMIT 1.
             */
            $stmt = $pdo->prepare("
                SELECT id, full_name, email, password_hash, role, is_active
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            $stmt->execute([
                'email' => $email,
            ]);

            $user = $stmt->fetch();

            /**
             * Comprobaciones:
             * 1. que el usuario exista
             * 2. que esté activo
             * 3. que la contraseña coincida con el hash almacenado
             */
            if (!$user) {
                $error = 'El correo electrónico o la contraseña no son correctos.';
            } elseif (!(bool) $user['is_active']) {
                $error = 'Tu cuenta está desactivada.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'El correo electrónico o la contraseña no son correctos.';
            } else {
                /**
                 * Regenera el ID de sesión por seguridad
                 * para evitar fijación de sesión.
                 */
                session_regenerate_id(true);

                /**
                 * Guarda en sesión solo los datos mínimos necesarios.
                 * No guardamos password_hash ni información innecesaria.
                 */
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];

                /**
                 * Redirige al panel privado de prueba.
                 */
                header('Location: ' . BASE_URL . '/admin/index.php');
                exit;
            }
        } catch (PDOException $e) {
            /**
             * En producción no conviene mostrar errores técnicos.
             * En local y desarrollo sí puede ayudar durante las pruebas.
             */
            if (APP_ENV === 'prod') {
                $error = 'Se ha producido un error al iniciar sesión.';
            } else {
                $error = 'Error al iniciar sesión: ' . $e->getMessage();
            }
        }
    }
}

/**
 * Carga la cabecera HTML común del proyecto.
 */
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-box">
    <h1>Iniciar sesión</h1>
    <p>Accede a JobSpot con tu correo electrónico y contraseña.</p>

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
                required
            >
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

        <button type="submit" class="btn-primary">Entrar</button>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>