<?php
// ==========================================================
// Create Post — handles Video / Audio / Photography / Blog
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../create_post.php");
    exit;
}

$user_id     = $_SESSION['user_id'];
$talent_type = $_POST['talent_type'] ?? '';
$title       = trim($_POST['title'] ?? '');
$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$content     = trim($_POST['content'] ?? ''); // used by blog only
$status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

$validTypes = ['video', 'audio', 'photography', 'blog'];
$backPage = in_array($talent_type, $validTypes) ? "../{$talent_type}_form.php" : '../create_post.php';

if (!in_array($talent_type, $validTypes) || $title === '') {
    header("Location: {$backPage}?error=missing_fields");
    exit;
}

// ----------------------------------------------------------
// Upload helpers
// ----------------------------------------------------------
function save_upload($fieldName, $destSubfolder, $allowedExt, $maxBytes)
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // nothing uploaded, not necessarily an error
    }
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed. Please try again.'];
    }

    $tmpPath  = $_FILES[$fieldName]['tmp_name'];
    $origName = $_FILES[$fieldName]['name'];
    $size     = $_FILES[$fieldName]['size'];
    $mime     = mime_content_type($tmpPath);
    $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        return ['error' => 'Unsupported file format: .' . $ext];
    }
    if ($size > $maxBytes) {
        return ['error' => 'File is too large.'];
    }

    $safeName  = uniqid($destSubfolder . '_', true) . '.' . $ext;
    $destDir   = __DIR__ . '/../uploads/' . $destSubfolder . '/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $destPath  = $destDir . $safeName;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        return ['error' => 'Could not save uploaded file.'];
    }

    return [
        'file_path' => 'uploads/' . $destSubfolder . '/' . $safeName,
        'file_name' => $origName,
        'mime_type' => $mime,
        'file_size' => $size,
    ];
}

$mediaRows = []; // each: ['media_type' => ..., 'file_path' => ..., 'file_name' => ..., 'mime_type' => ..., 'file_size' => ...]

switch ($talent_type) {

    case 'video':
        $video = save_upload('video_file', 'videos', ['mp4', 'mov', 'avi', 'mkv', 'webm'], 100 * 1024 * 1024);
        if ($video && isset($video['error'])) {
            header("Location: {$backPage}?error=" . urlencode($video['error']));
            exit;
        }
        if (!$video) {
            header("Location: {$backPage}?error=" . urlencode('A video file is required.'));
            exit;
        }
        $mediaRows[] = array_merge(['media_type' => 'video'], $video);

        // Cover/thumbnail is optional for video — publish fine without one
        $thumb = save_upload('thumbnail_file', 'thumbnails', ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
        if ($thumb && isset($thumb['error'])) {
            header("Location: {$backPage}?error=" . urlencode($thumb['error']));
            exit;
        }
        if ($thumb) {
            $mediaRows[] = array_merge(['media_type' => 'image'], $thumb);
        }
        break;

    case 'audio':
        $audio = save_upload('audio_file', 'audio', ['mp3', 'wav', 'aac', 'flac'], 100 * 1024 * 1024);
        if ($audio && isset($audio['error'])) {
            header("Location: {$backPage}?error=" . urlencode($audio['error']));
            exit;
        }
        if (!$audio) {
            header("Location: {$backPage}?error=" . urlencode('An audio file is required.'));
            exit;
        }
        $mediaRows[] = array_merge(['media_type' => 'audio'], $audio);

        // Cover image is REQUIRED for audio posts
        $thumb = save_upload('thumbnail_file', 'thumbnails', ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
        if ($thumb && isset($thumb['error'])) {
            header("Location: {$backPage}?error=" . urlencode($thumb['error']));
            exit;
        }
        if (!$thumb) {
            header("Location: {$backPage}?error=" . urlencode('A cover image is required for audio posts.'));
            exit;
        }
        $mediaRows[] = array_merge(['media_type' => 'image'], $thumb);
        break;

    case 'photography':
        $photo = save_upload('photo_file', 'photos', ['jpg', 'jpeg', 'png', 'webp'], 10 * 1024 * 1024);
        if ($photo && isset($photo['error'])) {
            header("Location: {$backPage}?error=" . urlencode($photo['error']));
            exit;
        }
        if (!$photo) {
            header("Location: {$backPage}?error=" . urlencode('A photo is required.'));
            exit;
        }
        $mediaRows[] = array_merge(['media_type' => 'image'], $photo);
        break;

    case 'blog':
        if (trim($content) === '') {
            header("Location: {$backPage}?error=" . urlencode('Blog content is required.'));
            exit;
        }
        $cover = save_upload('photo_file', 'photos', ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024);
        if ($cover && isset($cover['error'])) {
            header("Location: {$backPage}?error=" . urlencode($cover['error']));
            exit;
        }
        if (!$cover) {
            header("Location: {$backPage}?error=" . urlencode('A cover image is required for blog posts.'));
            exit;
        }
        $mediaRows[] = array_merge(['media_type' => 'image'], $cover);
        break;
}

// ----------------------------------------------------------
// Insert the post
// ----------------------------------------------------------
$stmt = $pdo->prepare(
    "INSERT INTO posts (user_id, title, description, content, talent_type, category, status)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([
    $user_id,
    $title,
    $description !== '' ? $description : null,
    $content !== '' ? $content : null,
    $talent_type,
    $category !== '' ? $category : null,
    $status,
]);
$postId = $pdo->lastInsertId();

// ----------------------------------------------------------
// Insert media rows
// ----------------------------------------------------------
if (!empty($mediaRows)) {
    $mediaStmt = $pdo->prepare(
        "INSERT INTO post_media (post_id, media_type, file_path, file_name, mime_type, file_size)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($mediaRows as $m) {
        $mediaStmt->execute([$postId, $m['media_type'], $m['file_path'], $m['file_name'], $m['mime_type'], $m['file_size']]);
    }
}

header("Location: ../dashboard.php?posted=1");
exit;
