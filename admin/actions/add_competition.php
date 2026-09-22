<?php
// ==========================================================
// Admin: Add Competition
// ==========================================================
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../competitions.php");
    exit;
}

$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date  = trim($_POST['event_date'] ?? '');
$event_time  = trim($_POST['event_time'] ?? '');
$venue       = trim($_POST['venue'] ?? '');
$deadline    = trim($_POST['registration_deadline'] ?? '');
$maxRaw      = trim($_POST['max_participants'] ?? '');

$errors = [];

if ($title === '' || mb_strlen($title) > 150) {
    $errors[] = 'Please enter a valid competition name (max 150 characters).';
}

$eventDateObj = DateTime::createFromFormat('Y-m-d', $event_date);
if (!$eventDateObj || $eventDateObj->format('Y-m-d') !== $event_date) {
    $errors[] = 'Please provide a valid competition date.';
}

if ($event_time !== '') {
    $t = DateTime::createFromFormat('H:i', $event_time);
    if (!$t) {
        $errors[] = 'Please provide a valid time.';
    }
}

$deadlineObj = null;
if ($deadline !== '') {
    $deadlineObj = DateTime::createFromFormat('Y-m-d', $deadline);
    if (!$deadlineObj || $deadlineObj->format('Y-m-d') !== $deadline) {
        $errors[] = 'Please provide a valid registration deadline.';
    } elseif ($eventDateObj && $deadlineObj > $eventDateObj) {
        $errors[] = 'The registration deadline cannot be after the competition date.';
    }
}

$max_participants = null;
if ($maxRaw !== '') {
    if (!ctype_digit($maxRaw) || (int)$maxRaw <= 0) {
        $errors[] = 'Maximum participants must be a positive whole number.';
    } else {
        $max_participants = (int)$maxRaw;
    }
}

if (mb_strlen($venue) > 200) {
    $errors[] = 'Venue/place is too long (max 200 characters).';
}

if (!empty($errors)) {
    $_SESSION['admin_form_errors'] = $errors;
    $_SESSION['admin_form_old'] = $_POST;
    header("Location: ../competitions.php?added=0");
    exit;
}

// A competition automatically starts as "upcoming"; it is treated as
// dynamically closed for registration once the deadline passes or the
// participant cap is reached — no separate "active/closed" toggle
// needed from the admin for that part.
$stmt = $pdo->prepare(
    "INSERT INTO competitions (title, description, event_date, event_time, venue, registration_deadline, max_participants, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'upcoming')"
);
$stmt->execute([
    $title,
    $description !== '' ? $description : null,
    $event_date,
    $event_time !== '' ? $event_time : null,
    $venue !== '' ? $venue : null,
    $deadline !== '' ? $deadline : null,
    $max_participants,
]);

header("Location: ../competitions.php?added=1");
exit;
