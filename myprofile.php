<?php
require_once 'includes/session_check.php';
require_once 'config/db.php';
require_once 'includes/leaderboard_helper.php';
require_once 'includes/notifications_helper.php';
require_once 'includes/post_render_helpers.php';

$user_id = $_SESSION['user_id'];

sync_competition_notifications($pdo, $user_id);
$notifications = get_notifications($pdo, $user_id, 10);
$unreadCount = get_unread_notification_count($pdo, $user_id);

$stmt = $pdo->prepare(
    "SELECT u.*, d.department_code, d.department_name
     FROM users u JOIN departments d ON d.department_id = u.department_id
     WHERE u.user_id = ?"
);
$stmt->execute([$user_id]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header("Location: index.php");
    exit;
}

$profileImgSrc = $profileUser['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($profileUser['profile_image']) : 'images/p4.jpeg';
$coverImgSrc = !empty($profileUser['cover_image']) ? 'uploads/profiles/' . htmlspecialchars($profileUser['cover_image']) : 'images/profile_cover.jpg';

// ---- Stats ----
$postCountStmt = $pdo->prepare("SELECT COUNT(*) AS c FROM posts WHERE user_id = ? AND status = 'published'");
$postCountStmt->execute([$user_id]);
$postCount = (int)$postCountStmt->fetch()['c'];

$likesStmt = $pdo->prepare(
    "SELECT COUNT(*) AS c FROM likes l
     JOIN posts p ON p.post_id = l.post_id
     WHERE p.user_id = ? AND p.status = 'published'"
);
$likesStmt->execute([$user_id]);
$likeCount = (int)$likesStmt->fetch()['c'];

$sharesStmt = $pdo->prepare(
    "SELECT COUNT(*) AS c FROM shares s
     JOIN posts p ON p.post_id = s.post_id
     WHERE p.user_id = ? AND p.status = 'published'"
);
$sharesStmt->execute([$user_id]);
$shareCount = (int)$sharesStmt->fetch()['c'];

$rankedUsers = get_leaderboard($pdo);
$myRank = null;
$myScore = 0;
foreach ($rankedUsers as $idx => $u) {
    if ((int)$u['user_id'] === (int)$user_id) {
        $myRank = $idx + 1;
        $myScore = (int)$u['score'];
        break;
    }
}

$myAchievements = get_user_achievements($pdo, $user_id, $myRank, $myScore);

// ---- Fetch a user's own posts by status (published or draft) ----
function fetch_own_posts(PDO $pdo, int $user_id, string $status, int $limit = 20): array
{
    $stmt = $pdo->prepare(
        "SELECT p.post_id, p.title, p.description, p.content, p.talent_type, p.status, p.created_at,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_count,
                (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active') AS comment_count,
                (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id) AS share_count,
                EXISTS(SELECT 1 FROM likes l2 WHERE l2.post_id = p.post_id AND l2.user_id = ?) AS user_liked
         FROM posts p
         WHERE p.user_id = ? AND p.status = ?
         ORDER BY p.created_at DESC
         LIMIT " . (int)$limit
    );
    $stmt->execute([$user_id, $user_id, $status]);
    return $stmt->fetchAll();
}

function fetch_media_for_posts(PDO $pdo, array $posts): array
{
    $mediaByPost = [];
    if (!empty($posts)) {
        $ids = array_column($posts, 'post_id');
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $mstmt = $pdo->prepare("SELECT * FROM post_media WHERE post_id IN ($ph) ORDER BY media_id ASC");
        $mstmt->execute($ids);
        foreach ($mstmt->fetchAll() as $m) {
            $mediaByPost[$m['post_id']][] = $m;
        }
    }
    return $mediaByPost;
}

$myPosts = fetch_own_posts($pdo, $user_id, 'published', 20);
$myMediaByPost = fetch_media_for_posts($pdo, $myPosts);

$myDrafts = fetch_own_posts($pdo, $user_id, 'draft', 20);
$myDraftMediaByPost = fetch_media_for_posts($pdo, $myDrafts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | My Profile</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/myprofile.css">
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
                    <li><a href="dashboard.php">Discover</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <li><a href="competition.php">Competitions</a></li>
                    <li><a href="myprofile.php" class="active">My Profile</a></li>
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
                    <img src="<?php echo $profileImgSrc; ?>" alt="profile">
                    <div class="profile_text">
                        <h4><?php echo htmlspecialchars($profileUser['full_name']); ?></h4>
                        <span><?php echo htmlspecialchars($profileUser['department_code']); ?></span>
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

    <!-- my profile body html start -->
    <div class="profile-container">

        <!-- profile header part html start -->
        <div class="profile-header">
            <div class="profile-cover">
                <img src="<?php echo $coverImgSrc; ?>" alt="Profile Cover">
            </div>
            <div class="profile-details">
                <div class="profile-picture">
                    <img src="<?php echo $profileImgSrc; ?>" alt="<?php echo htmlspecialchars($profileUser['full_name']); ?>">
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($profileUser['full_name']); ?></h1>
                    <p class="profile-dept">
                        <?php echo htmlspecialchars($profileUser['department_name']); ?>
                    </p>
                    <p class="profile-bio">
                        <?php echo $profileUser['bio'] ? htmlspecialchars($profileUser['bio']) : 'No bio added yet.'; ?>
                    </p>
                    <div class="profile-btns">
                        <button class="edit-btn" id="editProfileBtn" type="button">
                            <i class="fa-solid fa-pen"></i>
                            Edit Profile
                        </button>
                        <button class="share-btn share-profile-btn" type="button">
                            <i class="fa-solid fa-share-nodes"></i>
                            Share Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- profile header part html end -->

        <!-- profile statistics html start -->

        <div class="profile-stat">
            <div class="stat-box">
                <i class="fa-solid fa-file-lines"></i>
                <div>
                    <h3><?php echo number_format($postCount); ?></h3>
                    <p>Posts</p>
                </div>
            </div>
            <div class="stat-box">
                <i class="fa-solid fa-heart"></i>
                <div>
                    <h3><?php echo number_format($likeCount); ?></h3>
                    <p>Likes</p>
                </div>
            </div>
            <div class="stat-box">
                <i class="fa-solid fa-share-nodes"></i>
                <div>
                    <h3><?php echo number_format($shareCount); ?></h3>
                    <p>Shares</p>
                </div>
            </div>
            <div class="stat-box">
                <i class="fa-solid fa-ranking-star"></i>
                <div>
                    <h3><?php echo $myRank ? '#' . $myRank : '—'; ?></h3>
                    <p>Leaderboard Rank</p>
                </div>
            </div>
        </div>

        <!-- profile statistics html end -->
        
        <!-- profile content html start -->

        <div class="profile-content">
            <!-- left portion start -->
            <div class="profile-sidebar">
                <!-- about part start -->
                <div class="profile-card">
                    <div class="card-heading">
                        <h2>About Me</h2>
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="about-list">
                        <div class="about-item">
                            <span>Department</span>
                            <strong><?php echo htmlspecialchars($profileUser['department_name']); ?></strong>
                        </div>
                        <div class="about-item">
                            <span>Student ID</span>
                            <strong><?php echo htmlspecialchars($profileUser['student_id']); ?></strong>
                        </div>
                        <div class="about-item">
                            <span>Batch</span>
                            <strong><?php echo $profileUser['batch'] ? htmlspecialchars($profileUser['batch']) : '—'; ?></strong>
                        </div>
                        <div class="about-item">
                            <span>Email</span>
                            <strong><?php echo $profileUser['email'] ? htmlspecialchars($profileUser['email']) : '—'; ?></strong>
                        </div>
                        <div class="about-item">
                            <span>Bio</span>
                            <strong><?php echo $profileUser['bio'] ? htmlspecialchars($profileUser['bio']) : '—'; ?></strong>
                        </div>
                    </div>
                </div>
                <!-- about part finish -->
                
                <!-- achievements part start -->
                <div class="profile-card">
                    <div class="card-heading">
                        <h2>Achievements</h2>
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div class="achievement-list">
                        <?php if (empty($myAchievements)): ?>
                            <p style="padding:10px 0; color:#888;">No achievements yet. Keep creating and engaging to earn your first achievement!</p>
                        <?php else: ?>
                            <?php foreach ($myAchievements as $ach): ?>
                                <div class="achievement">
                                    <div class="achievement-icon">
                                        <i class="fa-solid <?php echo htmlspecialchars($ach['icon']); ?>"></i>
                                    </div>
                                    <div>
                                        <h4><?php echo htmlspecialchars($ach['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($ach['subtitle']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>  
                </div> 
                <!-- achievement part end --> 
            </div>
            <!-- left portion end  -->

            <!-- right portion that contains user posts start -->
            <div class="user-post">
                <div class="posts-header">
                    <div>
                        <h2>My Posts</h2>
                        <p>Your latest creations and submissions</p>
                    </div>
                    <span class="post-count"><?php echo number_format($postCount); ?> Posts</span>
                </div>

                <!-- Published / Draft tabs -->
                <div class="post-tabs">
                    <button type="button" class="post-tab active" data-tab="published">Published</button>
                    <button type="button" class="post-tab" data-tab="draft">Draft<?php echo count($myDrafts) > 0 ? ' (' . count($myDrafts) . ')' : ''; ?></button>
                </div>

                <!-- Published posts -->
                <div class="posts-grid post-tab-panel" id="publishedPostsGrid">
                    <?php if (empty($myPosts)): ?>
                        <p style="padding:20px; color:#888;">You haven't published anything yet. <a href="create_post.php">Create your first post</a>.</p>
                    <?php else: ?>
                        <?php foreach ($myPosts as $p): ?>
                            <?php
                                $pid = $p['post_id'];
                                $mediaHtml = render_post_media($p, $myMediaByPost);
                                $blogHtml = $p['talent_type'] === 'blog' ? render_blog_content($pid, $p['content']) : '';
                            ?>
                            <div class="post-card">
                                <div class="post-image">
                                    <?php echo $mediaHtml; ?>
                                    <span class="post-type">
                                        <i class="fa-solid <?php echo talent_icon($p['talent_type']); ?>"></i>
                                        <?php echo ucfirst($p['talent_type']); ?>
                                    </span>
                                </div>
                                <div class="post-content">
                                    <div class="post-user">
                                        <img src="<?php echo $profileImgSrc; ?>" alt="">
                                        <div>
                                            <h4><?php echo htmlspecialchars($profileUser['full_name']); ?></h4>
                                            <p><?php echo time_ago($p['created_at']); ?></p>
                                        </div>
                                    </div>
                                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                                    <?php if (!empty($p['description'])): ?>
                                        <p class="post-description"><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($blogHtml): ?>
                                        <?php echo $blogHtml; ?>
                                    <?php endif; ?>
                                    <div class="post-actions">
                                        <button type="button" class="like-btn disabled-action" data-post-id="<?php echo $pid; ?>" title="You can't like your own post">
                                            <i class="<?php echo $p['user_liked'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                                            <span class="like-count"><?php echo (int)$p['like_count']; ?></span>
                                        </button>
                                        <button type="button" class="comment-btn" data-post-id="<?php echo $pid; ?>">
                                            <i class="fa-regular fa-comment"></i>
                                            <span class="comment-count"><?php echo (int)$p['comment_count']; ?></span>
                                        </button>
                                        <button type="button" class="share-btn disabled-action" data-post-id="<?php echo $pid; ?>" data-title="<?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?>" title="You can't share your own post">
                                            <i class="fa-solid fa-share"></i>
                                            <span class="share-count"><?php echo (int)$p['share_count']; ?></span>
                                        </button>
                                    </div>
                                    <?php include 'includes/comment_section.php'; ?>
                                </div> 
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Draft posts -->
                <div class="posts-grid post-tab-panel" id="draftPostsGrid" style="display:none;">
                    <?php if (empty($myDrafts)): ?>
                        <p style="padding:20px; color:#888;">No drafts saved. Anything you save as a draft while creating a post will show up here.</p>
                    <?php else: ?>
                        <?php foreach ($myDrafts as $p): ?>
                            <?php
                                $pid = $p['post_id'];
                                $mediaHtml = render_post_media($p, $myDraftMediaByPost);
                                $blogHtml = $p['talent_type'] === 'blog' ? render_blog_content($pid, $p['content']) : '';
                            ?>
                            <div class="post-card draft-post-card">
                                <div class="post-image">
                                    <?php echo $mediaHtml; ?>
                                    <span class="post-type draft-badge">
                                        <i class="fa-solid fa-file-pen"></i>
                                        Draft
                                    </span>
                                </div>
                                <div class="post-content">
                                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                                    <?php if (!empty($p['description'])): ?>
                                        <p class="post-description"><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($blogHtml): ?>
                                        <?php echo $blogHtml; ?>
                                    <?php endif; ?>
                                    <form method="post" action="actions/publish_post.php" class="publish-draft-form">
                                        <input type="hidden" name="post_id" value="<?php echo $pid; ?>">
                                        <button type="submit" class="publish-draft-btn">
                                            <i class="fa-solid fa-upload"></i>
                                            Publish
                                        </button>
                                    </form>
                                </div> 
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- post as grid end -->

            </div>
            <!-- user post end -->
        </div>
        <!-- profile content html end -->

    </div>
    <!-- my profile body html end -->

    <!-- Edit Profile Modal -->
    <div class="edit-profile-overlay" id="editProfileOverlay">
        <div class="edit-profile-modal">
            <div class="edit-profile-header">
                <h3>Edit Profile</h3>
                <button type="button" id="editProfileClose" class="edit-profile-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="post" action="actions/update_profile_photos.php" enctype="multipart/form-data" class="edit-profile-form">
                <div class="edit-profile-field">
                    <label>Profile Photo</label>
                    <img src="<?php echo $profileImgSrc; ?>" alt="" class="edit-profile-preview" id="profilePhotoPreview">
                    <input type="file" name="profile_photo" id="profilePhotoInput" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="edit-profile-field">
                    <label>Cover Photo</label>
                    <img src="<?php echo $coverImgSrc; ?>" alt="" class="edit-profile-preview cover-preview" id="coverPhotoPreview">
                    <input type="file" name="cover_photo" id="coverPhotoInput" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <button type="submit" class="edit-profile-save">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Share Menu (shared) -->
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

<script src="js/dashboard.js"></script>    
<script src="js/search.js"></script>
<script src="js/myprofile.js"></script>
<script src="js/feed_actions.js"></script>
</body>
</html>