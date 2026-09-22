<?php
// ==========================================================
// Admin: Update a report's status (Pending / Reviewed / Resolved)
// ==========================================================
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../reports.php");
    exit;
}

$report_id = (int)($_POST['report_id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowedStatuses = ['pending', 'reviewed', 'resolved'];

if ($report_id > 0 && in_array($status, $allowedStatuses, true)) {
    $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
    $stmt->execute([$status, $report_id]);
}

header("Location: ../reports.php");
exit;
