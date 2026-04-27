<?php
// company/job-delete.php — Cerrar o eliminar una oferta de trabajo
// La acción se recibe por GET (?action=close o ?action=delete).
// No mostramos HTML, solo procesamos y redirigimos.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
requireRole('company');

$user = $_SESSION['user'];
$pdo  = getPDO();

// Obtenemos el perfil de empresa del usuario actual
$stmtCompany = $pdo->prepare("SELECT id FROM companies WHERE owner_user_id = :uid");
$stmtCompany->execute(['uid' => $user['id']]);
$company = $stmtCompany->fetch();

if (!$company) {
    header('Location: ' . BASE_URL . '/company/index.php');
    exit;
}

// =========================================================
// OBTENEMOS LOS PARÁMETROS DE LA URL
// =========================================================
$jobId = (int) ($_GET['id']     ?? 0);
$action = trim($_GET['action']  ?? '');

// Si falta algún parámetro, redirigimos sin hacer nada
if ($jobId === 0 || !in_array($action, ['close', 'delete'])) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

// =========================================================
// VERIFICAMOS QUE LA OFERTA PERTENECE A ESTA EMPRESA
// =========================================================
// Esto es crítico para la seguridad: una empresa no puede
// cerrar ni borrar ofertas de otras empresas.
$stmtJob = $pdo->prepare("
    SELECT id, status FROM jobs WHERE id = :id AND company_id = :cid
");
$stmtJob->execute(['id' => $jobId, 'cid' => $company['id']]);
$job = $stmtJob->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/company/jobs.php');
    exit;
}

// =========================================================
// EJECUTAMOS LA ACCIÓN CORRESPONDIENTE
// =========================================================

if ($action === 'close' && $job['status'] === 'published') {
    // Cerrar = marcar como 'closed'. Los datos se conservan.
    // Los candidatos ya no podrán aplicar, pero el historial queda.
    $stmtClose = $pdo->prepare("
        UPDATE jobs SET status = 'closed' WHERE id = :id AND company_id = :cid
    ");
    $stmtClose->execute(['id' => $jobId, 'cid' => $company['id']]);

} elseif ($action === 'delete' && in_array($job['status'], ['draft', 'rejected'])) {
    // Eliminar = borrar físicamente la oferta.
    // Solo permitimos borrar borradores o rechazadas.
    // Las candidaturas asociadas se eliminan en cascada (ON DELETE CASCADE en el schema).
    $stmtDelete = $pdo->prepare("
        DELETE FROM jobs WHERE id = :id AND company_id = :cid
    ");
    $stmtDelete->execute(['id' => $jobId, 'cid' => $company['id']]);
}

// Redirigimos al listado de ofertas en todos los casos
header('Location: ' . BASE_URL . '/company/jobs.php');
exit;
