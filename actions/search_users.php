<?php
// ==========================================================
// Search students/users (AJAX) — returns JSON { results: [...] }
// Matches by full name or student ID, using prepared statements.
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

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode(['results' => []]);
    exit;
}

$like = '%' . $q . '%';

$stmt = $pdo->prepare(
    "SELECT u.user_id, u.full_name, u.student_id, u.profile_image, d.department_code
     FROM users u
     JOIN departments d ON d.department_id = u.department_id
     WHERE u.status = 'active'
       AND u.user_id <> ?
       AND (u.full_name LIKE ? OR u.student_id LIKE ?)
     ORDER BY u.full_name ASC
     LIMIT 8"
);
$stmt->execute([(int)$_SESSION['user_id'], $like, $like]);
$rows = $stmt->fetchAll();

$results = array_map(function ($row) {
    return [
        'user_id'        => (int)$row['user_id'],
        'full_name'      => $row['full_name'],
        'student_id'     => $row['student_id'],
        'department_code' => $row['department_code'],
        'profile_image'  => $row['profile_image'] ? 'uploads/profiles/' . $row['profile_image'] : 'images/p4.jpeg',
    ];
}, $rows);

echo json_encode(['results' => $results]);
