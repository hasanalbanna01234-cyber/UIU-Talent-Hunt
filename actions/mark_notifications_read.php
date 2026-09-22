<?php
// ==========================================================
// Mark all of the current user's notifications as read
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

require_once '../config/db.php';

$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);

echo json_encode(['success' => true]);
