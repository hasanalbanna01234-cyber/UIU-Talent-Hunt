<?php
// ==========================================================
// Register / Cancel registration for a competition
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$competition_id = (int)($_POST['competition_id'] ?? 0);

if ($competition_id > 0) {
    $stmt = $pdo->prepare(
        "SELECT registration_id, status FROM competition_registrations
         WHERE competition_id = ? AND user_id = ?"
    );
    $stmt->execute([$competition_id, $user_id]);
    $existing = $stmt->fetch();

    if ($existing && $existing['status'] === 'registered') {
        // Cancelling is always allowed, regardless of the deadline or
        // participant cap — a student can always withdraw.
        $upd = $pdo->prepare("UPDATE competition_registrations SET status = 'cancelled' WHERE registration_id = ?");
        $upd->execute([$existing['registration_id']]);
    } else {
        // A NEW registration (or re-registering after a previous cancel)
        // must be checked against the deadline and the participant cap
        // here on the server — never trust that the button was disabled
        // on the page the request came from.
        $compStmt = $pdo->prepare(
            "SELECT registration_deadline, max_participants,
                    (SELECT COUNT(*) FROM competition_registrations r
                     WHERE r.competition_id = c.competition_id AND r.status = 'registered') AS participant_count
             FROM competitions c
             WHERE c.competition_id = ?"
        );
        $compStmt->execute([$competition_id]);
        $comp = $compStmt->fetch();

        if (!$comp) {
            header("Location: ../competition.php");
            exit;
        }

        $deadlinePassed = $comp['registration_deadline']
            ? (strtotime($comp['registration_deadline'] . ' 23:59:59') < time())
            : false;
        $isFull = $comp['max_participants'] !== null
            && (int)$comp['participant_count'] >= (int)$comp['max_participants'];

        if ($deadlinePassed || $isFull) {
            header("Location: ../competition.php?closed=1");
            exit;
        }

        if ($existing) {
            // previously cancelled -> re-register
            $upd = $pdo->prepare("UPDATE competition_registrations SET status = 'registered' WHERE registration_id = ?");
            $upd->execute([$existing['registration_id']]);
        } else {
            $ins = $pdo->prepare("INSERT INTO competition_registrations (competition_id, user_id) VALUES (?, ?)");
            $ins->execute([$competition_id, $user_id]);
        }
    }
}

header("Location: ../competition.php");
exit;

