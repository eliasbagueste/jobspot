<?php
require_once __DIR__ . '/config/database.php';

$pdo = getPDO();

// Estadísticas reales para mostrar en la portada
$totalJobs      = (int) $pdo->query("SELECT COUNT(*) FROM jobs      WHERE status = 'published'")->fetchColumn();
$totalCompanies = (int) $pdo->query("SELECT COUNT(*) FROM companies WHERE is_verified = 1")->fetchColumn();
$totalCandidates = (int) $pdo->query("SELECT COUNT(*) FROM users   WHERE role = 'candidate' AND is_active = 1")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<!-- =====================================================
     HERO
====================================================== -->
<section class="home-hero">
    <div class="home-hero-inner">
        <h1 class="home-hero-title">
            Encuentra tu próximo<br>
            <span class="home-hero-accent">empleo</span>, hoy.
        </h1>
        <p class="home-hero-sub">
            Conectamos candidatos con empresas locales de forma rápida,
            sencilla y sin intermediarios.
        </p>
        <div class="home-hero-actions">
            <a href="<?= BASE_URL; ?>/jobs.php" class="btn-hero-primary">
                Ver ofertas de empleo
            </a>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-hero-secondary">
                    Crear cuenta gratis
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Imagen corporativa en el lado derecho del hero -->
    <div class="home-hero-image" aria-hidden="true">
        <img src="https://images.unsplash.com/photo-1758873269317-51888e824b28?w=900&auto=format&fit=crop&q=80" alt="">
    </div>

    <!-- Decoración geométrica -->
    <div class="home-hero-deco" aria-hidden="true">
        <div class="deco-circle deco-1"></div>
        <div class="deco-circle deco-2"></div>
        <div class="deco-circle deco-3"></div>
    </div>
</section>

<!-- =====================================================
     ESTADÍSTICAS
====================================================== -->
<section class="home-stats">
    <div class="home-stat">
        <span class="home-stat-num"><?= $totalJobs; ?></span>
        <span class="home-stat-label">Ofertas activas</span>
    </div>
    <div class="home-stat-divider"></div>
    <div class="home-stat">
        <span class="home-stat-num"><?= $totalCompanies; ?></span>
        <span class="home-stat-label">Empresas verificadas</span>
    </div>
    <div class="home-stat-divider"></div>
    <div class="home-stat">
        <span class="home-stat-num"><?= $totalCandidates; ?></span>
        <span class="home-stat-label">Candidatos registrados</span>
    </div>
</section>

<!-- =====================================================
     PARA QUIÉN ES
====================================================== -->
<section class="home-section">
    <h2 class="home-section-title">¿Quién usa JobSpot?</h2>
    <p class="home-section-sub">Una plataforma pensada para dos perfiles con un objetivo común.</p>

    <div class="home-cards">

        <div class="home-feature-card">
            <img src="https://images.unsplash.com/photo-1698047681432-006d2449c631?w=600&auto=format&fit=crop&q=80"
                 alt="Candidata revisando un currículum"
                 class="home-feature-img">
            <h3>Candidatos</h3>
            <p>Explora ofertas de empleo, aplica con un clic y lleva el seguimiento de todas tus candidaturas desde un panel centralizado.</p>
            <ul class="home-feature-list">
                <li>✓ Búsqueda por categoría, modalidad y contrato</li>
                <li>✓ Aplicación con mensaje personalizado</li>
                <li>✓ Estado de cada candidatura en tiempo real</li>
            </ul>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-feature">
                    Registrarse como candidato →
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL; ?>/jobs.php" class="btn-feature">
                    Ver ofertas →
                </a>
            <?php endif; ?>
        </div>

        <div class="home-feature-card home-feature-card--dark">
            <img src="https://images.unsplash.com/photo-1698047681820-f26b00b6c639?w=600&auto=format&fit=crop&q=80"
                 alt="Empresa en proceso de selección"
                 class="home-feature-img home-feature-img--dark">
            <h3>Empresas</h3>
            <p>Publica tus vacantes, revisa las candidaturas recibidas y gestiona todo el proceso de selección desde tu panel de empresa.</p>
            <ul class="home-feature-list">
                <li>✓ Crea y edita ofertas en minutos</li>
                <li>✓ Recibe candidaturas directamente</li>
                <li>✓ Acepta o rechaza con un solo clic</li>
            </ul>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-feature btn-feature--light">
                    Registrar empresa →
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL; ?>/jobs.php" class="btn-feature btn-feature--light">
                    Ver ofertas →
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- =====================================================
     CÓMO FUNCIONA
====================================================== -->
<section class="home-section home-section--gray">
    <h2 class="home-section-title">¿Cómo funciona?</h2>
    <p class="home-section-sub">Tres pasos para encontrar trabajo o cubrir una vacante.</p>

    <div class="home-steps">
        <div class="home-step">
            <div class="home-step-num">1</div>
            <h4>Crea tu cuenta</h4>
            <p>Regístrate como candidato o empresa. Es gratis y solo tarda un minuto.</p>
        </div>
        <div class="home-step-arrow">→</div>
        <div class="home-step">
            <div class="home-step-num">2</div>
            <h4>Explora o publica</h4>
            <p>Los candidatos buscan y filtran ofertas. Las empresas crean sus vacantes.</p>
        </div>
        <div class="home-step-arrow">→</div>
        <div class="home-step">
            <div class="home-step-num">3</div>
            <h4>Conecta</h4>
            <p>Los candidatos aplican y las empresas gestionan el proceso desde su panel.</p>
        </div>
    </div>
</section>

<!-- =====================================================
     CTA FINAL
====================================================== -->
<?php if (!isset($_SESSION['user'])): ?>
<section class="home-cta">
    <h2>¿Listo para empezar?</h2>
    <p>Únete a JobSpot y da el siguiente paso en tu carrera o en tu empresa.</p>
    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px;">
        <a href="<?= BASE_URL; ?>/register.php" class="btn-hero-primary">
            Crear cuenta gratis
        </a>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-hero-secondary">
            Ver ofertas
        </a>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
