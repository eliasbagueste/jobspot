<?php
// admin/companies.php — Gestión y verificación de empresas

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('admin');

$pdo = getPDO();

// =========================================================
// ACCIONES POST: verificar o revocar verificación
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyId = (int) ($_POST['company_id'] ?? 0);
    $action    = $_POST['action'] ?? '';

    if ($companyId > 0 && in_array($action, ['verify', 'unverify'])) {
        $newValue = $action === 'verify' ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE companies SET is_verified = :v WHERE id = :id");
        $stmt->execute(['v' => $newValue, 'id' => $companyId]);
    }

    header('Location: ' . BASE_URL . '/admin/companies.php');
    exit;
}

// =========================================================
// LISTADO DE EMPRESAS
// =========================================================
$companies = $pdo->query("
    SELECT
        co.id,
        co.legal_name,
        co.brand_name,
        co.location,
        co.is_verified,
        co.created_at,
        u.full_name  AS owner_name,
        u.email      AS owner_email,
        COUNT(j.id)  AS total_jobs
    FROM companies co
    JOIN users u ON u.id = co.owner_user_id
    LEFT JOIN jobs j ON j.company_id = co.id
    GROUP BY co.id
    ORDER BY co.is_verified ASC, co.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<a href="<?= BASE_URL; ?>/admin/index.php" class="btn-link" style="display:inline-block; margin-bottom:1rem;">
    ← Volver al panel
</a>

<section class="card">
    <h1>Empresas</h1>
    <p style="color:#64748b; margin-bottom:1.5rem;">
        Las empresas deben estar verificadas para poder publicar ofertas.
    </p>

    <?php if (empty($companies)): ?>
        <p>No hay empresas registradas todavía.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Responsable</th>
                    <th>Ubicación</th>
                    <th>Ofertas</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $co): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($co['brand_name']); ?></strong><br>
                            <span style="font-size:0.8rem; color:#64748b;">
                                <?= htmlspecialchars($co['legal_name']); ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($co['owner_name']); ?><br>
                            <span style="font-size:0.8rem; color:#64748b;">
                                <?= htmlspecialchars($co['owner_email']); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($co['location'] ?? '—'); ?></td>
                        <td><?= (int) $co['total_jobs']; ?></td>
                        <td>
                            <?php if ($co['is_verified']): ?>
                                <span class="badge badge-active">Verificada</span>
                            <?php else: ?>
                                <span class="badge badge-company">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.85rem; color:#64748b;">
                            <?= date('d/m/Y', strtotime($co['created_at'])); ?>
                        </td>
                        <td>
                            <form method="post" action="<?= BASE_URL; ?>/admin/companies.php" style="display:inline;">
                                <input type="hidden" name="company_id" value="<?= $co['id']; ?>">
                                <?php if ($co['is_verified']): ?>
                                    <input type="hidden" name="action" value="unverify">
                                    <button type="submit" class="btn-delete">Revocar</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="verify">
                                    <button type="submit" class="btn-edit">Verificar</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
