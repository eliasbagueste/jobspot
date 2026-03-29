<?php
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-box">
    <h1>Registrate</h1>
    <p>Ingresa tus datos para crear una cuenta.</p>

    <form action="<?= BASE_URL; ?>/register.php" method="post" class="auth-form" novalidate>
    
        <div class="form-group">
            <label for="full_name">Nombre completo</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
    
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="role">Tipo de cuenta</label>
            <select id="role" name="role" required>
                <option value="">Selecciona una opción</option>
                <option value="candidate">Candidato</option>
                <option value="company">Empresa</option>
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