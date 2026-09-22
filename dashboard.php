<?php
require_once 'includes/session_check.php'; // redirects to index.php if not logged in
require_once 'config/db.php';

// Fetch the logged-in user's info (with department name) for the navbar
$stmt = $pdo->prepare(
    "SELECT u.full_name, u.profile_image, d.department_code
     FROM users u
     JOIN departments d ON d.department_id = u.department_id
     WHERE u.user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

// Fallback in the unlikely case the user row was deleted after login
if (!$currentUser) {
    $currentUser = ['full_name' => 'User', 'profile_image' => null, 'department_code' => ''];
}
$profileImgSrc = $currentUser['profile_image'] ? 'uploads/profiles/' . $currentUser['profile_image'] : 'images/p4.jpeg';

// ----------------------------------------------------------
// Real discover feed: published posts with engagement counts
// ----------------------------------------------------------
$currentUserId = $_SESSION['user_id'];

$postsStmt = $pdo->prepare(
    "SELECT p.post_id, p.title, p.description, p.content, p.talent_type, p.category, p.created_at,
            u.user_id AS author_id, u.full_name AS author_name, u.profile_image AS author_image,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_count,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active') AS comment_count,
            (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id) AS share_count,
            EXISTS(SELECT 1 FROM likes l2 WHERE l2.post_id = p.post_id AND l2.user_id = ?) AS user_liked,
            EXISTS(SELECT 1 FROM shares s2 WHERE s2.post_id = p.post_id AND s2.user_id = ?) AS user_shared,
            EXISTS(SELECT 1 FROM reports rp WHERE rp.post_id = p.post_id AND rp.user_id = ?) AS user_reported
     FROM posts p
     JOIN users u ON u.user_id = p.user_id
     WHERE p.status = 'published'
     ORDER BY p.created_at DESC
     LIMIT 20"
);
$postsStmt->execute([$currentUserId, $currentUserId, $currentUserId]);
$feedPosts = $postsStmt->fetchAll();

$trendingStmt = $pdo->prepare(
    "SELECT u.user_id, u.full_name, u.profile_image, COUNT(l.like_id) AS total_likes
     FROM likes l
     JOIN posts p ON p.post_id = l.post_id AND p.status = 'published'
     JOIN users u ON u.user_id = p.user_id
     WHERE l.user_id <> p.user_id AND u.status = 'active'
     GROUP BY u.user_id, u.full_name, u.profile_image
     ORDER BY total_likes DESC, u.full_name ASC
     LIMIT 3"
);
$trendingStmt->execute();
$trendingTalents = $trendingStmt->fetchAll();

foreach ($trendingTalents as $idx => $user) {
    $categoryStmt = $pdo->prepare(
        "SELECT p.talent_type
         FROM posts p
         LEFT JOIN likes l ON l.post_id = p.post_id AND l.user_id <> p.user_id
         WHERE p.user_id = ? AND p.status = 'published'
         GROUP BY p.talent_type
         ORDER BY COUNT(l.like_id) DESC, COUNT(p.post_id) DESC, p.talent_type ASC
         LIMIT 1"
    );
    $categoryStmt->execute([$user['user_id']]);
    $trendingTalents[$idx]['top_category'] = $categoryStmt->fetchColumn() ?: 'new';
}

// Pull all media for these posts in one query, grouped by post_id
$mediaByPost = [];
if (!empty($feedPosts)) {
    $ids = array_column($feedPosts, 'post_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $mediaStmt = $pdo->prepare("SELECT * FROM post_media WHERE post_id IN ($placeholders) ORDER BY media_id ASC");
    $mediaStmt->execute($ids);
    foreach ($mediaStmt->fetchAll() as $m) {
        $mediaByPost[$m['post_id']][] = $m;
    }
}

require_once 'includes/post_render_helpers.php';

require_once 'includes/leaderboard_helper.php';
$topUsers = get_leaderboard($pdo, 3);

// Real upcoming events/competitions for the sidebar widget
$upcomingEventsStmt = $pdo->prepare(
    "SELECT competition_id, title, venue, event_date, event_time
     FROM competitions
     WHERE status IN ('upcoming', 'active') AND event_date >= CURDATE()
     ORDER BY event_date ASC
     LIMIT 3"
);
$upcomingEventsStmt->execute();
$upcomingEvents = $upcomingEventsStmt->fetchAll();

require_once 'includes/notifications_helper.php';
sync_competition_notifications($pdo, $currentUserId);
$notifications = get_notifications($pdo, $currentUserId, 10);
$unreadCount = get_unread_notification_count($pdo, $currentUserId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <!-- navbar part html start -->
    <header class="navbar">
        <div class="logo">
            <img src="images/logo.png" alt="UIU Talent Hunt Logo" class="circle-logo">
        </div>
        <!-- menu part html -->
        <nav>
            <div class="menu">
                <ul>
                    <li><a href="dashboard.php" class="active">Discover</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <li><a href="competition.php">Competitions</a></li>
                    <li><a href="myprofile.php">My Profile</a></li>
                </ul>
            </div>
        </nav>
        <!-- right side html-->
        <div class="right_nav">
            <!-- search box html -->
            <div class="search-box" id="searchBox">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search talents, students..." autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
            <!-- notification part html start -->
            <div class="notification" id="notification">
                <i class="fa-regular fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-count"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
                <div class="notification-menu" id="notificationmenu">
                    <div class="notification-header">
                        <h3>Notification</h3>
                        <span class="notification-unread-label"><?php echo $unreadCount > 0 ? $unreadCount . ' New' : 'All caught up'; ?></span>
                    </div>
                    <?php if (empty($notifications)): ?>
                        <div class="notification-item">
                            <div class="notification-content">
                                <p>No notifications yet.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <!-- event notification -->
                            <div class="notification-item">
                                <div class="notification-content">
                                    <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($n['message']); ?></p>
                                    <span><?php echo notification_time_ago($n['created_at']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- view all -->
                    <a href="competition.php" class="notification-view-all">
                        View All Events
                        <i class="fa-solid fa-arrow-right"></i>
                    </a> 
                </div>
            </div>
            <!-- notification part html end -->
            <!-- profile part html -->
            <div class="profile" id="profile">
                <div class="profile_info">
                    <img src="<?php echo htmlspecialchars($profileImgSrc); ?>" alt="profile">
                    <div class="profile_text">
                        <h4><?php echo htmlspecialchars($currentUser['full_name']); ?></h4>
                        <span><?php echo htmlspecialchars($currentUser['department_code']); ?></span>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-down arrow"></i>
                <div class="profile_menu" id="profilemenu">
                    <a href="settings.html">
                        <i class="fa-solid fa-gear"></i>
                        Settings
                    </a>
                    <hr>
                    <a href="actions/logout.php" class="logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    <!-- navbar part html end -->

    <!-- main body html start -->
    <div class="main-container">
        <!-- left content -->
        <div class="left_content">
            <!-- Banner part -->
            <div class="banner">
                <div class="slides">
                    <div class="slide active" style="background-image: url('images/banner1.jpg');">
                        <div class="banner_overlay">
                            <div class="banner_text">
                                <h1>Discover Hidden <br> Talents of UIU</h1>
                                <p>Explore the latest creations from the UIU community, support your favorite talents, and share your own journey.</p>
                                <a href="create_post.php" class="create-post-btn">
                                    <i class="fa-solid fa-plus"></i>
                                    Create Post
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="slide" style="background-image: url('images/banner2.jpg');">
                        <div class="banner_overlay">
                            <div class="banner_text">
                                <h1> Show Your <br> Creativity</h1>
                                <p>Upload videos, music, stories and inspire thousands of UIU students.</p>
                                <a href="create_post.php" class="create-post-btn">
                                    <i class="fa-solid fa-plus"></i>
                                    Create Post
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="slide" style="background-image: url('images/banner3.jpg');">
                        <div class="banner_overlay">
                            <div class="banner_text">
                                <h1> Become The <br> Next Champion</h1>
                                <p>Earn likes, climb the leaderboard and become the most popular talent in UIU.</p>
                                <a href="create_post.php" class="create-post-btn">
                                    <i class="fa-solid fa-plus"></i>
                                    Create Post
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- discover feed html start -->
            <div class="discover_feed">
                <div class="header">
                    <div>
                        <h2>Discover Feed</h2>
                        <p>Explore the latest talents shared by the UIU community</p>
                    </div>
                </div>
                <!-- feed grid html -->
                <?php $feedVisibleCount = 7; $feedHasExtra = count($feedPosts) > $feedVisibleCount; ?>
                <div class="feed-grid">
                    <?php if (empty($feedPosts)): ?>
                        <div class="quote-card">
                            <i class="fa-solid fa-quote-left"></i>
                            <p>No talents shared yet. Be the first to post something!</p>
                            <span>- UIU Talent Hunt</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feedPosts as $i => $post): ?>
                            <?php if ($feedHasExtra && $i === $feedVisibleCount): ?>
                                </div>
                                <div class="view-all-feed-wrapper">
                                    <button type="button" id="viewAllPostsBtn" class="view-all-feed-btn">
                                        View All
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="feed-grid extra-feed-posts" id="extraFeedPosts">
                            <?php endif; ?>
                            <?php
                                $authorImg = $post['author_image'] ? 'uploads/profiles/' . htmlspecialchars($post['author_image']) : 'images/p4.jpeg';
                                $heartClass = $post['user_liked'] ? 'fa-solid' : 'fa-regular';
                                $isOwn = (int)$post['author_id'] === (int)$currentUserId;
                                $alreadyShared = (bool)$post['user_shared'];
                                $pid = $post['post_id'];

                                $likeClass = 'like-btn' . ($isOwn ? ' disabled-action' : '');
                                $likeTitle = $isOwn ? ' title="You can\'t like your own post"' : '';

                                $shareClass = 'share-btn' . ($isOwn ? ' disabled-action' : ($alreadyShared ? ' already-shared' : ''));
                                $shareTitle = $isOwn ? ' title="You can\'t share your own post"' : ($alreadyShared ? ' title="You already shared this post"' : '');

                                $alreadyReported = (bool)$post['user_reported'];
                                $reportClass = 'report-btn' . ($isOwn ? ' disabled-action' : ($alreadyReported ? ' already-reported' : ''));
                                $reportTitle = $isOwn ? ' title="You can\'t report your own post"' : ($alreadyReported ? ' title="You already reported this post"' : ' title="Report this post"');

                                $mediaHtml = render_post_media($post, $mediaByPost);
                                $blogHtml = $post['talent_type'] === 'blog' ? render_blog_content($pid, $post['content']) : '';
                            ?>
                            <?php if ($i === 0): ?>
                                <!-- large card: most recent post -->
                                <div class="feed-card large-card">
                                    <div class="card-image">
                                        <?php echo $mediaHtml; ?>
                                    </div>
                                    <div class="card-content">
                                        <div class="user-info">
                                            <img src="<?php echo $authorImg; ?>" alt="">
                                            <div>
                                                <h4><?php echo htmlspecialchars($post['author_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($post['title']); ?></p>
                                            </div>
                                        </div>
                                        <?php if (!empty($post['description'])): ?>
                                            <p class="post-description"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                                        <?php endif; ?>
                                        <?php if ($blogHtml): ?>
                                            <?php echo $blogHtml; ?>
                                        <?php endif; ?>
                                        <div class="card-actions">
                                            <span class="<?php echo $likeClass; ?>" data-post-id="<?php echo $pid; ?>"<?php echo $likeTitle; ?>>
                                                <i class="<?php echo $heartClass; ?> fa-heart"></i>
                                                <span class="like-count"><?php echo (int)$post['like_count']; ?></span>
                                            </span>
                                            <span class="comment-btn" data-post-id="<?php echo $pid; ?>">
                                                <i class="fa-regular fa-comment"></i>
                                                <span class="comment-count"><?php echo (int)$post['comment_count']; ?></span>
                                            </span>
                                            <span class="<?php echo $shareClass; ?>" data-post-id="<?php echo $pid; ?>" data-title="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>"<?php echo $shareTitle; ?>>
                                                <i class="fa-solid fa-share"></i>
                                                <span class="share-count"><?php echo (int)$post['share_count']; ?></span>
                                            </span>
                                            <span class="<?php echo $reportClass; ?>" data-post-id="<?php echo $pid; ?>"<?php echo $reportTitle; ?>>
                                                <i class="fa-solid fa-flag"></i>
                                            </span>
                                        </div>
                                        <?php include 'includes/comment_section.php'; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- small card -->
                                <div class="feed-card">
                                    <div class="card-image">
                                        <?php echo $mediaHtml; ?>
                                    </div>
                                    <div class="card-content">
                                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <?php if (!empty($post['description'])): ?>
                                            <p class="post-description"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                                        <?php endif; ?>
                                        <?php if ($blogHtml): ?>
                                            <?php echo $blogHtml; ?>
                                        <?php endif; ?>
                                        <div class="user-row">
                                            <span><?php echo htmlspecialchars($post['author_name']); ?></span>
                                            <span class="<?php echo $likeClass; ?>" data-post-id="<?php echo $pid; ?>"<?php echo $likeTitle; ?>>
                                                <i class="<?php echo $heartClass; ?> fa-heart"></i>
                                                <span class="like-count"><?php echo (int)$post['like_count']; ?></span>
                                            </span>
                                            <span class="comment-btn" data-post-id="<?php echo $pid; ?>">
                                                <i class="fa-regular fa-comment"></i>
                                                <span class="comment-count"><?php echo (int)$post['comment_count']; ?></span>
                                            </span>
                                            <span class="<?php echo $shareClass; ?>" data-post-id="<?php echo $pid; ?>" data-title="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>"<?php echo $shareTitle; ?>>
                                                <i class="fa-solid fa-share"></i>
                                                <span class="share-count"><?php echo (int)$post['share_count']; ?></span>
                                            </span>
                                            <span class="<?php echo $reportClass; ?>" data-post-id="<?php echo $pid; ?>"<?php echo $reportTitle; ?>>
                                                <i class="fa-solid fa-flag"></i>
                                            </span>
                                        </div>
                                        <?php include 'includes/comment_section.php'; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- quote card -->
                        <div class="quote-card">
                            <i class="fa-solid fa-quote-left"></i>
                            <p>Every talent shines brightest when it is shared</p>
                            <span>- UIU Talent Hunt</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <!-- discover feed html end -->
        </div>

        <!-- right sidebar -->
        <div class="right-sidebar">

            <!-- trending talents card -->
            <div class="sidebar-card">
                <div class="sidebar-title">
                    <h3>🔥 Trending Talents</h3>
                </div>
                <?php if (empty($trendingTalents)): ?>
                    <p style="padding:10px 0; color:#888;">No talents yet — be the first!</p>
                <?php else: ?>
                    <?php foreach ($trendingTalents as $u): ?>
                        <div class="trending-item">
                            <img src="<?php echo $u['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($u['profile_image']) : 'images/p4.jpeg'; ?>" alt="">
                            <div class="trending-info">
                                <h4><?php echo htmlspecialchars($u['full_name']); ?></h4>
                                <p><?php echo htmlspecialchars(ucfirst($u['top_category'] ?? 'New')); ?> • <?php echo number_format((int)$u['total_likes']); ?> Likes</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- leaderboard card -->
            <div class="sidebar-card">
                <div class="sidebar-title">
                    <h3>🏆 Leaderboard</h3>
                    <a href="leaderboard.php">View All</a>
                </div>
                <?php if (empty($topUsers)): ?>
                    <p style="padding:10px 0; color:#888;">Leaderboard will populate as posts get engagement.</p>
                <?php else: ?>
                    <?php $medalClasses = ['gold', 'silver', 'bronze']; ?>
                    <?php foreach ($topUsers as $idx => $u): ?>
                        <div class="leader-item">
                            <div class="leader-left">
                                <?php if ($idx < 3): ?>
                                    <span class="medal <?php echo $medalClasses[$idx]; ?>">
                                        <i class="fa-solid fa-medal"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="rank"><?php echo $idx + 1; ?></span>
                                <?php endif; ?>
                                <img src="<?php echo $u['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($u['profile_image']) : 'images/p4.jpeg'; ?>" alt="">
                                <div>
                                    <h4><?php echo htmlspecialchars($u['full_name']); ?></h4>
                                    <p><?php echo number_format($u['score']); ?> pts</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- upcoming events card -->
            <div class="sidebar-card">
                <div class="sidebar-title">
                    <h3>📅 Upcoming Events</h3>
                    <a href="competition.php">View All</a>
                </div>
                <?php if (empty($upcomingEvents)): ?>
                    <p style="padding:10px 0; color:#888;">No upcoming events right now.</p>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $ev): ?>
                        <div class="event-item">
                            <div class="event-date">
                                <span><?php echo date('d', strtotime($ev['event_date'])); ?></span>
                                <small><?php echo strtoupper(date('M', strtotime($ev['event_date']))); ?></small>
                            </div>
                            <div class="event-info">
                                <h4><?php echo htmlspecialchars($ev['title']); ?></h4>
                                <p>
                                    <?php echo htmlspecialchars($ev['venue'] ?: 'TBA'); ?>
                                    <?php if ($ev['event_time']): ?>
                                        • <?php echo date('g:i A', strtotime($ev['event_time'])); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div> 
    </div>    
    
    <!-- main body html end -->
    
    <!-- footer part html start -->
    <footer class="footer-container">
        <div class="footer-content">
            <div class="footer-about">
                <img src="images/logo.png" alt="UIU Talent Hunt Logo" class="circle-logo">
                <h2>UIU Talent Hunt</h2>
                <p>
                   Discover • Inspire • Showcase 
                </p>
            </div>
            <div class="address">
                <h3>Address</h3>
                <p>United International University, UIU Permanent Campus</p>
                <p>United City, Madani Avenue</p>
                <p>Notun Bazar, 100 - Feet, Dhaka - 1212</p>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <a href="dashboard.php">Discover</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="competition.php">Competitions</a>
                <a href="myprofile.php">My Posts</a>
            </div>
            <div class="footer-social">
                <h3>Follow Us</h3>
                <a href="#"><i class="fa-brands fa-facebook"></i>Facebook</a>
                <a href="#"><i class="fa-brands fa-instagram"></i>Instagram</a>
                <a href="#"><i class="fa-brands fa-x-twitter"></i>Twitter</a>
            </div>
        </div>
        <hr>
        <div class="footer-bottom">
            <p>
               &copy; 2026 UIU Talent Hunt. All Rights Reserved. 
            </p>
            <p>
                Developed by Team UIU Talent Hunt | Department of CSE, UIU
            </p>
        </div>
    </footer> 
    <!-- footer part html end -->

    <!-- ============================================== -->
    <!-- Share Menu (shared, positioned near clicked button) -->
    <!-- ============================================== -->
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

    <!-- ============================================== -->
    <!-- Report Menu (shared, positioned near clicked flag icon) -->
    <!-- ============================================== -->
    <div class="share-menu" id="reportMenu">
        <button type="button" class="share-option report-option" data-reason="Spam">
            <i class="fa-solid fa-ban"></i> Spam
        </button>
        <button type="button" class="share-option report-option" data-reason="Inappropriate Content">
            <i class="fa-solid fa-triangle-exclamation"></i> Inappropriate Content
        </button>
        <button type="button" class="share-option report-option" data-reason="Harassment">
            <i class="fa-solid fa-user-slash"></i> Harassment
        </button>
        <button type="button" class="share-option report-option" data-reason="Copyright Issue">
            <i class="fa-solid fa-copyright"></i> Copyright Issue
        </button>
        <button type="button" class="share-option report-option" data-reason="Other">
            <i class="fa-solid fa-ellipsis"></i> Other
        </button>
    </div>

    <!-- small inline toast for share/comment/report feedback (no browser alerts) -->
    <div class="inline-toast" id="inlineToast"></div>

<script src="js/dashboard.js"></script>
<script src="js/search.js"></script>
<script src="js/feed_actions.js"></script>
</body>
</html>