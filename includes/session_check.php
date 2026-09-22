<?php
// ==============================
// Session guard
// ==============================
// Include this at the very top of any page that requires a logged-in user,
// BEFORE any HTML is output.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Defensive check: the session may reference a user_id that no longer
// exists (e.g. the database was re-imported/reseeded after login). If
// we don't catch that here, every query further down the page that
// joins on users.user_id — or inserts a row with a foreign key back to
// it, like notifications — fails with an uncaught PDOException instead
// of a clean redirect. Checking once here keeps every protected page safe.
require_once __DIR__ . '/../config/db.php';

$__sessionUserCheck = $pdo->prepare("SELECT 1 FROM users WHERE user_id = ?");
$__sessionUserCheck->execute([$_SESSION['user_id']]);

if (!$__sessionUserCheck->fetch()) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php?error=" . urlencode('Your session has expired. Please log in again.'));
    exit;
}
