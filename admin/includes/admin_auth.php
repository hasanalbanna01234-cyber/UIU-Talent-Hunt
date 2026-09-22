<?php
// ==========================================================
// Admin session guard
// ==========================================================
// Include this at the very top of every admin/*.php page,
// before any HTML is output. Completely separate from the
// student session system in includes/session_check.php —
// this only ever checks/sets $_SESSION['is_admin'], so a
// logged-in student never gets treated as an admin and vice
// versa, even though both share the same PHP session cookie.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit;
}
