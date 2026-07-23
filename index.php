<?php
require_once __DIR__ . '/config/database.php';

$pdo = getPDO();

// Estadísticas reales para mostrar en la portada
$totalJobs      = (int) $pdo->query("SELECT COUNT(*) FROM jobs      WHERE status = 'published'")->fetchColumn();
$totalCompanies = (int) $pdo->query("SELECT COUNT(*) FROM companies WHERE is_verified = 1")->fetchColumn();
$totalCandidates = (int) $pdo->query("SELECT COUNT(*) FROM users   WHERE role = 'candidate' AND is_active = 1")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Sección hero: titular principal y botones de llamada a la acción -->
<section class="home-hero">
    <div class="home-hero-inner">
        <h1 class="home-hero-title">
            Find your next<br>
            <span class="home-hero-accent">job</span>, today.
        </h1>
        <p class="home-hero-sub">
            We connect candidates with local companies quickly,
            simply and without middlemen.
        </p>
        <div class="home-hero-actions">
            <a href="<?= BASE_URL; ?>/jobs.php" class="btn-hero-primary">
                View job listings
            </a>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-hero-secondary">
                    Create free account
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

<!-- Estadísticas en tiempo real extraídas de la base de datos -->
<section class="home-stats">
    <div class="home-stat">
        <span class="home-stat-num"><?= $totalJobs; ?></span>
        <span class="home-stat-label">Active jobs</span>
    </div>
    <div class="home-stat-divider"></div>
    <div class="home-stat">
        <span class="home-stat-num"><?= $totalCompanies; ?></span>
        <span class="home-stat-label">Verified companies</span>
    </div>
    <div class="home-stat-divider"></div>
    <div class="home-stat">
        <span class="home-stat-num"><?= $totalCandidates; ?></span>
        <span class="home-stat-label">Registered candidates</span>
    </div>
</section>

<!-- Sección "¿Quién usa JobSpot?" con las tarjetas de candidato y empresa -->
<section class="home-section">
    <h2 class="home-section-title">Who uses JobSpot?</h2>
    <p class="home-section-sub">A platform designed for two profiles with a common goal.</p>

    <div class="home-cards">

        <div class="home-feature-card">
            <img src="https://images.unsplash.com/photo-1698047681432-006d2449c631?w=600&auto=format&fit=crop&q=80"
                 alt="Candidate reviewing a resume"
                 class="home-feature-img">
            <h3>Candidates</h3>
            <p>Explore job listings, apply with one click and track all your applications from a centralised dashboard.</p>
            <ul class="home-feature-list">
                <li>✓ Search by category, work mode and contract type</li>
                <li>✓ Apply with a personalised message</li>
                <li>✓ Real-time status for every application</li>
            </ul>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-feature">
                    Sign up as a candidate →
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL; ?>/jobs.php" class="btn-feature">
                    View jobs →
                </a>
            <?php endif; ?>
        </div>

        <div class="home-feature-card home-feature-card--dark">
            <img src="https://images.unsplash.com/photo-1698047681820-f26b00b6c639?w=600&auto=format&fit=crop&q=80"
                 alt="Company during a hiring process"
                 class="home-feature-img home-feature-img--dark">
            <h3>Companies</h3>
            <p>Post your vacancies, review the applications you receive and manage the whole hiring process from your company dashboard.</p>
            <ul class="home-feature-list">
                <li>✓ Create and edit job listings in minutes</li>
                <li>✓ Receive applications directly</li>
                <li>✓ Accept or reject with a single click</li>
            </ul>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL; ?>/register.php" class="btn-feature btn-feature--light">
                    Register your company →
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL; ?>/jobs.php" class="btn-feature btn-feature--light">
                    View jobs →
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Sección "¿Cómo funciona?" con los tres pasos del proceso -->
<section class="home-section home-section--gray">
    <h2 class="home-section-title">How does it work?</h2>
    <p class="home-section-sub">Three steps to find a job or fill a vacancy.</p>

    <div class="home-steps">
        <div class="home-step">
            <div class="home-step-num">1</div>
            <h4>Create your account</h4>
            <p>Sign up as a candidate or company. It's free and only takes a minute.</p>
        </div>
        <div class="home-step-arrow">→</div>
        <div class="home-step">
            <div class="home-step-num">2</div>
            <h4>Explore or post</h4>
            <p>Candidates search and filter jobs. Companies create their vacancies.</p>
        </div>
        <div class="home-step-arrow">→</div>
        <div class="home-step">
            <div class="home-step-num">3</div>
            <h4>Connect</h4>
            <p>Candidates apply and companies manage the process from their dashboard.</p>
        </div>
    </div>
</section>

<!-- CTA final: solo se muestra si el usuario no tiene sesión iniciada -->
<?php if (!isset($_SESSION['user'])): ?>
<section class="home-cta">
    <h2>Ready to get started?</h2>
    <p>Join JobSpot and take the next step in your career or your company.</p>
    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px;">
        <a href="<?= BASE_URL; ?>/register.php" class="btn-hero-primary">
            Create free account
        </a>
        <a href="<?= BASE_URL; ?>/jobs.php" class="btn-hero-secondary">
            View jobs
        </a>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
