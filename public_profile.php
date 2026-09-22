<?php
require_once 'includes/session_check.php';
require_once 'config/db.php';
require_once 'includes/post_render_helpers.php';

$profileId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$profileId || $profileId < 1) {
    header('Location: dashboard.php');
    exit;
}

$userStmt = $pdo->prepare(
    "SELECT u.user_id, u.full_name, u.student_id, u.profile_image, u.cover_image,
            d.department_code, d.department_name
     FROM users u
     JOIN departments d ON d.department_id = u.department_id
     WHERE u.user_id = ? AND u.status = 'active'"
);
$userStmt->execute([$profileId]);
$profileUser = $userStmt->fetch();

if (!$profileUser) {
    http_response_code(404);
    exit('Student profile not found.');
}

$currentUserId = (int)$_SESSION['user_id'];
$postsStmt = $pdo->prepare(
    "SELECT p.post_id, p.title, p.description, p.content, p.talent_type, p.created_at,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_count,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active') AS comment_count,
            (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id) AS share_count,
            EXISTS(SELECT 1 FROM likes l2 WHERE l2.post_id = p.post_id AND l2.user_id = ?) AS user_liked,
            EXISTS(SELECT 1 FROM shares s2 WHERE s2.post_id = p.post_id AND s2.user_id = ?) AS user_shared
     FROM posts p
     WHERE p.user_id = ? AND p.status = 'published'
     ORDER BY p.created_at DESC
     LIMIT 50"
);
$postsStmt->execute([$currentUserId, $currentUserId, $profileId]);
$posts = $postsStmt->fetchAll();

$mediaByPost = [];
if ($posts) {
    $postIds = array_column($posts, 'post_id');
    $placeholders = implode(',', array_fill(0, count($postIds), '?'));
    $mediaStmt = $pdo->prepare("SELECT * FROM post_media WHERE post_id IN ($placeholders) ORDER BY media_id ASC");
    $mediaStmt->execute($postIds);
    foreach ($mediaStmt->fetchAll() as $media) {
        $mediaByPost[$media['post_id']][] = $media;
    }
}

$profileImage = $profileUser['profile_image']
    ? 'uploads/profiles/' . htmlspecialchars($profileUser['profile_image'])
    : 'images/p4.jpeg';
$coverImage = !empty($profileUser['cover_image'])
    ? 'uploads/profiles/' . htmlspecialchars($profileUser['cover_image'])
    : 'images/profile_cover.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profileUser['full_name']); ?> | UIU Talent Hunt</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/myprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="logo"><img src="images/logo.png" alt="UIU Talent Hunt Logo" class="circle-logo"></div>
        <nav>
            <div class="menu">
                <ul>
                    <li><a href="dashboard.php">Discover</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <li><a href="competition.php">Competitions</a></li>
                    <li><a href="myprofile.php">My Profile</a></li>
                </ul>
            </div>
        </nav>
        <div class="right_nav">
            <div class="search-box" id="searchBox">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search talents, students..." autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>
    </header>

    <main class="profile-container">
        <section class="profile-header">
            <div class="profile-cover"><img src="<?php echo $coverImage; ?>" alt=""></div>
            <div class="profile-details">
                <div class="profile-picture"><img src="<?php echo $profileImage; ?>" alt=""></div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($profileUser['full_name']); ?></h1>
                    <p class="profile-dept">
                        <?php echo htmlspecialchars($profileUser['student_id']); ?> ·
                        <?php echo htmlspecialchars($profileUser['department_name'] ?: $profileUser['department_code']); ?>
                    </p>
                </div>
            </div>
        </section>

        <section class="user-post">
            <div class="posts-header">
                <div>
                    <h2>Published Posts</h2>
                    <p><?php echo count($posts); ?> public post<?php echo count($posts) === 1 ? '' : 's'; ?></p>
                </div>
            </div>
            <div class="posts-grid">
                <?php if (!$posts): ?>
                    <p style="padding:20px; color:#888;">This student has not published any posts yet.</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                            $postId = (int)$post['post_id'];
                            $pid = $postId;
                            $mediaHtml = render_post_media($post, $mediaByPost);
                            $blogHtml = $post['talent_type'] === 'blog'
                                ? render_blog_content($postId, $post['content'])
                                : '';
                            $isOwnPost = $currentUserId === (int)$profileUser['user_id'];
                            $likeClass = 'like-btn' . ($isOwnPost ? ' disabled-action' : '');
                            $shareClass = 'share-btn' . ($isOwnPost ? ' disabled-action' : ($post['user_shared'] ? ' already-shared' : ''));
                        ?>
                        <article class="post-card">
                            <div class="post-image">
                                <?php echo $mediaHtml; ?>
                                <span class="post-type">
                                    <i class="fa-solid <?php echo talent_icon($post['talent_type']); ?>"></i>
                                    <?php echo htmlspecialchars(ucfirst($post['talent_type'])); ?>
                                </span>
                            </div>
                            <div class="post-content">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <?php if ($post['description']): ?>
                                    <p class="post-description"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                                <?php endif; ?>
                                <?php echo $blogHtml; ?>
                                <div class="post-actions">
                                    <button type="button" class="<?php echo $likeClass; ?>" data-post-id="<?php echo $postId; ?>">
                                        <i class="<?php echo $post['user_liked'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                                        <span class="like-count"><?php echo (int)$post['like_count']; ?></span>
                                    </button>
                                    <button type="button" class="comment-btn" data-post-id="<?php echo $postId; ?>">
                                        <i class="fa-regular fa-comment"></i>
                                        <span class="comment-count"><?php echo (int)$post['comment_count']; ?></span>
                                    </button>
                                    <button type="button" class="<?php echo $shareClass; ?>" data-post-id="<?php echo $postId; ?>" data-title="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>">
                                        <i class="fa-solid fa-share"></i>
                                        <span class="share-count"><?php echo (int)$post['share_count']; ?></span>
                                    </button>
                                </div>
                                <?php include 'includes/comment_section.php'; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <div class="share-menu" id="shareMenu">
        <button type="button" class="share-option" data-action="copy">
            <i class="fa-solid fa-link"></i> Copy Link
        </button>
        <button type="button" class="share-option" data-action="whatsapp">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp
        </button>
        <button type="button" class="share-option" data-action="facebook">
            <i class="fa-brands fa-facebook"></i> Facebook
        </button>
        <button type="button" class="share-option" data-action="twitter">
            <i class="fa-brands fa-x-twitter"></i> Twitter / X
        </button>
        <button type="button" class="share-option" data-action="email">
            <i class="fa-solid fa-envelope"></i> Email
        </button>
    </div>
    <div class="inline-toast" id="inlineToast"></div>
    <script src="js/search.js"></script>
    <script src="js/feed_actions.js"></script>
</body>
</html>
