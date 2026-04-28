<?php
// candidate/favorite-toggle.php — Añadir o quitar una oferta de favoritos (responde JSON)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'candidate') {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$user  = $_SESSION['user'];
$pdo   = getPDO();
$jobId = (int) ($_POST['job_id'] ?? 0);

if ($jobId === 0) {
    echo json_encode(['error' => 'invalid']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM favorite_jobs WHERE candidate_user_id = :uid AND job_id = :jid");
$stmt->execute(['uid' => $user['id'], 'jid' => $jobId]);

if ($stmt->fetch()) {
    $pdo->prepare("DELETE FROM favorite_jobs WHERE candidate_user_id = :uid AND job_id = :jid")
        ->execute(['uid' => $user['id'], 'jid' => $jobId]);
    echo json_encode(['favorited' => false]);
} else {
    $pdo->prepare("INSERT INTO favorite_jobs (candidate_user_id, job_id) VALUES (:uid, :jid)")
        ->execute(['uid' => $user['id'], 'jid' => $jobId]);
    echo json_encode(['favorited' => true]);
}
exit;
