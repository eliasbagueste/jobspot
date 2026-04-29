    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <?= APP_NAME; ?>
                    <p>Conectando talento con oportunidades locales.</p>
                </div>
                <div class="footer-col">
                    <span class="footer-col-title">Plataforma</span>
                    <a href="<?= BASE_URL; ?>/jobs.php">Ver ofertas</a>
                    <a href="<?= BASE_URL; ?>/register.php">Crear cuenta</a>
                    <a href="<?= BASE_URL; ?>/login.php">Iniciar sesión</a>
                </div>
                <div class="footer-col">
                    <span class="footer-col-title">Síguenos</span>
                    <div class="footer-social-icons">
                        <a href="#" class="social-icon" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="social-icon" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?= date('Y'); ?> <?= APP_NAME; ?>. Todos los derechos reservados.</span>
                <span>Proyecto DAW · Elías Bagüeste Amate</span>
            </div>
        </div>
    </footer>

    <script>
        // Gestión de favoritos con fetch para no recargar la página.
        // Busco todos los botones de corazón y les pongo un listener.
        document.querySelectorAll('.btn-favorite').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const jobId = btn.dataset.jobId;
                const icon  = btn.querySelector('i');
                const label = btn.querySelector('span');

                // Llamo al endpoint PHP que hace el toggle en la BD
                fetch('/candidate/favorite-toggle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'job_id=' + jobId
                })
                .then(r => r.json())
                .then(function (data) {
                    if (data.favorited) {
                        // Ahora es favorito: corazón relleno y rojo
                        btn.dataset.favorited = '1';
                        icon.className        = 'fas fa-heart';
                        btn.style.color       = '#ef4444';
                        btn.title             = 'Quitar de favoritos';
                        if (label) label.textContent = 'Guardada en favoritos';
                    } else {
                        // Ya no es favorito: corazón vacío y gris
                        btn.dataset.favorited = '0';
                        icon.className        = 'far fa-heart';
                        btn.style.color       = '#cbd5e1';
                        btn.title             = 'Añadir a favoritos';
                        if (label) label.textContent = 'Guardar en favoritos';
                        // Si estamos en la página de favoritos, animamos y quitamos la tarjeta
                        const card = btn.closest('.job-card');
                        if (card && window.location.pathname.includes('favorites')) {
                            card.style.transition = 'opacity 0.3s';
                            card.style.opacity    = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                    }
                });
            });
        });
    </script>

    <script>
    // Menú hamburguesa para móvil.
    // Al pulsar el botón se añade/quita la clase nav-open en el nav.
    // También cierro el menú si el usuario hace clic fuera.
    (function () {
        var toggle = document.getElementById('navToggle');
        var nav    = document.getElementById('mainNav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            nav.classList.toggle('nav-open');
            toggle.classList.toggle('nav-toggle--open'); // anima las líneas a una X
        });

        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('nav-open');
                toggle.classList.remove('nav-toggle--open');
            }
        });
    })();
    </script>
</body>
</html>
