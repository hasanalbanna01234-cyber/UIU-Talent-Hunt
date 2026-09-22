<?php
require_once 'includes/session_check.php';
require_once 'config/db.php';
require_once 'includes/leaderboard_helper.php';
require_once 'includes/notifications_helper.php';

$currentUserId = $_SESSION['user_id'];
sync_competition_notifications($pdo, $currentUserId);
$notifications = get_notifications($pdo, $currentUserId, 10);
$unreadCount = get_unread_notification_count($pdo, $currentUserId);

$stmt = $pdo->prepare(
    "SELECT u.full_name, u.profile_image, d.department_code
     FROM users u JOIN departments d ON d.department_id = u.department_id
     WHERE u.user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch() ?: ['full_name' => 'User', 'profile_image' => null, 'department_code' => ''];
$profileImgSrc = $currentUser['profile_image'] ? 'uploads/profiles/' . $currentUser['profile_image'] : 'images/p4.jpeg';

// Leaderboard filters — validated against a strict whitelist before
// ever reaching the query (see get_leaderboard()).
$allowedCategories = ['all', 'video', 'audio', 'photography', 'blog'];
$allowedTimeRanges = ['this_month', 'last_month', 'this_year', 'all_time'];

$selectedCategory = $_GET['category'] ?? 'all';
$selectedTime = $_GET['time'] ?? 'all_time';

if (!in_array($selectedCategory, $allowedCategories, true)) {
    $selectedCategory = 'all';
}
if (!in_array($selectedTime, $allowedTimeRanges, true)) {
    $selectedTime = 'all_time';
}

$rankedUsers = get_leaderboard($pdo, null, $selectedCategory, $selectedTime); // full ranking, no limit

function talent_category_label($type)
{
    return [
        'video' => 'Video',
        'audio' => 'Audio',
        'photography' => 'Photography',
        'blog' => 'Blog',
    ][$type] ?? 'New';
}

function talent_category_icon($type)
{
    return [
        'video' => 'fa-video',
        'audio' => 'fa-music',
        'photography' => 'fa-camera',
        'blog' => 'fa-pen-nib',
    ][$type] ?? 'fa-star';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | Leaderboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/leaderboard.css">
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
                    <li><a href="leaderboard.php" class="active">Leaderboard</a></li>
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

    <!-- leaderboard main content html start -->
    <div class="leaderboard-container">
        <!-- page-heading -->
        <div class="leaderboard-header">
            <div>
                <h1>Leaderboard</h1>
                <div class="title-line"></div>
                <p>See the most outstanding talents in UIU Talent Hunt.</p>
            </div>

            <!-- month filter -->
            <div class="time-filter">
                <i class="fa-regular fa-calendar"></i>
                <select id="timeFilterSelect" onchange="window.location.href='leaderboard.php?category=<?php echo urlencode($selectedCategory); ?>&time=' + this.value;">
                    <option value="this_month" <?php echo $selectedTime === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="last_month" <?php echo $selectedTime === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                    <option value="this_year" <?php echo $selectedTime === 'this_year' ? 'selected' : ''; ?>>This Year</option>
                    <option value="all_time" <?php echo $selectedTime === 'all_time' ? 'selected' : ''; ?>>All Time</option>
                </select>
            </div> 
        </div>

        <!-- main leaderboard layout -->
        <div class="leaderboard-layout">
            <!-- left side -->
            <div class="leaderboard-left">
                <div class="category-filter">
                    <a href="leaderboard.php?category=all&time=<?php echo urlencode($selectedTime); ?>" class="category-btn <?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">
                        All Categories
                    </a>
                    <a href="leaderboard.php?category=video&time=<?php echo urlencode($selectedTime); ?>" class="category-btn <?php echo $selectedCategory === 'video' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-video"></i>
                        Video
                    </a>
                    <a href="leaderboard.php?category=audio&time=<?php echo urlencode($selectedTime); ?>" class="category-btn <?php echo $selectedCategory === 'audio' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-music"></i>
                        Audio
                    </a>
                    <a href="leaderboard.php?category=photography&time=<?php echo urlencode($selectedTime); ?>" class="category-btn <?php echo $selectedCategory === 'photography' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-camera"></i>
                        Photography
                    </a>
                    <a href="leaderboard.php?category=blog&time=<?php echo urlencode($selectedTime); ?>" class="category-btn <?php echo $selectedCategory === 'blog' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-pen-nib"></i>
                        Blog
                    </a>
                </div>
                <!-- leaderboard table -->
                <div class="leaderboard-card">
                    <div class="table-header">
                        <div>Rank</div>
                        <div>Talent</div>
                        <div>Category</div>
                        <div>Score</div>
                        <div>Badges</div>
                    </div>

                    <?php if (empty($rankedUsers)): ?>
                        <p style="padding:20px; color:#888;">No ranked talents yet — post something to get on the board!</p>
                    <?php else: ?>
                        <?php $medalClass = ['gold', 'silver', 'bronze']; ?>
                        <?php $placeClass = ['first-place', 'second-place', 'third-place']; ?>
                        <?php foreach (array_slice($rankedUsers, 0, 10) as $idx => $u): ?>
                            <?php
                                $rowClass = $idx < 3 ? 'leaderboard-row ' . $placeClass[$idx] : 'leaderboard-row';
                                $img = $u['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($u['profile_image']) : 'images/p4.jpeg';
                                $catType = $u['top_category'];
                            ?>
                            <div class="<?php echo $rowClass; ?>">
                                <div class="rank">
                                    <?php if ($idx < 3): ?>
                                        <span class="medal <?php echo $medalClass[$idx]; ?>">
                                            <i class="fa-solid fa-medal"></i>
                                        </span>
                                        <strong><?php echo $idx + 1; ?></strong>
                                    <?php else: ?>
                                        <?php echo $idx + 1; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="talent-info">
                                    <img src="<?php echo $img; ?>" alt="">
                                    <div>
                                        <h3><?php echo htmlspecialchars($u['full_name']); ?></h3>
                                        <p><?php echo htmlspecialchars($u['department_code']); ?></p>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($catType): ?>
                                        <span class="category-tag <?php echo htmlspecialchars($catType === 'photography' ? 'photography' : $catType); ?>">
                                            <i class="fa-solid <?php echo talent_category_icon($catType); ?>"></i>
                                            <?php echo talent_category_label($catType); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="category-tag">— </span>
                                    <?php endif; ?>
                                </div>
                                <div class="score">
                                    <?php echo number_format($u['score']); ?>
                                </div>
                                <div class="badges">
                                    <?php if ($idx === 0 && $u['score'] > 0): ?>
                                        <span class="badge-crown"><i class="fa-solid fa-crown"></i></span>
                                    <?php elseif ($u['score'] >= 20): ?>
                                        <span class="badge star"><i class="fa-solid fa-star"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div> 
            </div>
            <!--right side  -->
            <div class="leaderboard-right">
            <!-- top talent -->
                <div class="top-talent-card">
                    <div class="top-talent-title">
                        <i class="fa-solid fa-crown"></i>
                        <h2>Top Talent</h2>
                    </div>
                    <div class="trophy">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <?php if (!empty($rankedUsers)): ?>
                        <div class="winner-info">
                            <img src="<?php echo $rankedUsers[0]['profile_image'] ? 'uploads/profiles/' . htmlspecialchars($rankedUsers[0]['profile_image']) : 'images/p4.jpeg'; ?>" alt="Top Talent">
                            <div>
                                <h3><?php echo htmlspecialchars($rankedUsers[0]['full_name']); ?></h3>
                                <p><?php echo htmlspecialchars($rankedUsers[0]['department_code']); ?></p>
                            </div>
                        </div>
                        <div class="winner-score">
                            <?php echo number_format($rankedUsers[0]['score']); ?>
                        </div>
                    <?php else: ?>
                        <p style="text-align:center; color:#888;">No talents yet</p>
                    <?php endif; ?>
                    <p class="score-label">
                        Total Score
                    </p>
                </div>

                <!-- how leaderboard works -->
                <div class="leaderboard-info-card">
                    <h2>
                        How Leaderboard Works
                    </h2>
                    <div class="info-item">
                        <div class="info-icon orange">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div>
                            <h3>Earn Points</h3>
                            <p>
                                Upload content and gain points based on likes, comments & shares.
                            </p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon blue">
                            <i class="fa-solid fa-arrow-up"></i>
                        </div>
                        <div>
                            <h3>Climb the Ranks</h3>
                            <p>
                                The more engagement you get, the higher you climb.
                            </p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon purple">
                            <i class="fa-solid fa-rotate"></i>
                        </div>
                        <div>
                            <h3>Monthly Reset</h3>
                            <p>
                                Leaderboard resets every month. New chance to shine!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>        
    <!-- leaderboard main content html end -->
    
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
<script src="js/dashboard.js"></script>
<script src="js/search.js"></script>
</body>
</html>