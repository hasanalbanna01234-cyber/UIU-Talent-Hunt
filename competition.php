<?php
require_once 'includes/session_check.php';
require_once 'config/db.php';
require_once 'includes/notifications_helper.php';

$user_id = $_SESSION['user_id'];

sync_competition_notifications($pdo, $user_id);
$notifications = get_notifications($pdo, $user_id, 10);
$unreadCount = get_unread_notification_count($pdo, $user_id);

$stmt = $pdo->prepare(
    "SELECT u.full_name, u.profile_image, d.department_code
     FROM users u JOIN departments d ON d.department_id = u.department_id
     WHERE u.user_id = ?"
);
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch() ?: ['full_name' => 'User', 'profile_image' => null, 'department_code' => ''];
$profileImgSrc = $currentUser['profile_image'] ? 'uploads/profiles/' . $currentUser['profile_image'] : 'images/p4.jpeg';

// Real competitions, with participant counts and whether the current user is registered
$compStmt = $pdo->prepare(
    "SELECT c.*,
            (SELECT COUNT(*) FROM competition_registrations r WHERE r.competition_id = c.competition_id AND r.status = 'registered') AS participant_count,
            EXISTS(
                SELECT 1 FROM competition_registrations r2
                WHERE r2.competition_id = c.competition_id AND r2.user_id = ? AND r2.status = 'registered'
            ) AS is_registered
     FROM competitions c
     ORDER BY c.event_date ASC"
);
$compStmt->execute([$user_id]);
$competitions = $compStmt->fetchAll();

function competition_icon($title)
{
    $t = strtolower($title);
    if (strpos($t, 'video') !== false) return ['fa-video', 'video'];
    if (strpos($t, 'audio') !== false || strpos($t, 'music') !== false) return ['fa-music', 'audio'];
    if (strpos($t, 'photo') !== false) return ['fa-camera', 'photography'];
    if (strpos($t, 'blog') !== false || strpos($t, 'writ') !== false) return ['fa-pen-nib', 'blog'];
    return ['fa-trophy', 'video'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | Competition</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/competition.css">
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
                    <li><a href="competition.php" class="active">Competitions</a></li>
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
    <div class="competition-container">
        <!-- heading start -->
        <div class="competition-header">
            <div>
                <h1>Competitions</h1>
                <div class="title-line"></div>
                <p>Participate inexciting competitions and showcase your talent to the UIU community.</p>
            </div>
        </div>
        <!-- heading end -->
        <?php if (isset($_GET['closed'])): ?>
            <div style="background:#FDECEA; color:#C0392B; padding:12px 18px; border-radius:10px; margin-bottom:18px; font-size:14px;">
                Registration is closed for that competition (the deadline has passed or it is full).
            </div>
        <?php endif; ?>
        <!-- competition list start -->
        <div class="competition-list">
            <?php if (empty($competitions)): ?>
                <p style="padding:20px; color:#888;">No competitions have been announced yet. Check back soon!</p>
            <?php else: ?>
                <?php foreach ($competitions as $c): ?>
                    <?php
                        [$icon, $iconClass] = competition_icon($c['title']);

                        // Registration closes automatically once the deadline passes
                        // OR the participant cap is reached — calculated here, and
                        // enforced again server-side in actions/register_competition.php.
                        $deadlinePassed = $c['registration_deadline']
                            ? (strtotime($c['registration_deadline'] . ' 23:59:59') < time())
                            : false;
                        $isFull = $c['max_participants'] !== null
                            && (int)$c['participant_count'] >= (int)$c['max_participants'];
                        $registrationClosed = $deadlinePassed || $isFull;

                        $deadlineLabel = $c['status'] === 'upcoming'
                            ? 'Starts: ' . date('F j, Y', strtotime($c['event_date']))
                            : 'Deadline: ' . date('F j, Y', strtotime($c['registration_deadline'] ?? $c['event_date']));
                        $statusLabel = ucfirst($c['status']);

                        $participantsLabel = $c['max_participants'] !== null
                            ? (int)$c['participant_count'] . ' / ' . (int)$c['max_participants']
                            : (int)$c['participant_count'];

                        if ($c['is_registered']) {
                            $buttonLabel = 'Registered ✓';
                        } elseif ($registrationClosed) {
                            $buttonLabel = 'Registration Closed';
                        } else {
                            $buttonLabel = $c['status'] === 'upcoming' ? 'View Competition' : 'Participate Now';
                        }
                    ?>
                    <div class="competition-card">
                        <!-- left -->
                        <div class="competition-main">
                            <div class="competition-icon <?php echo $iconClass; ?>">
                                <i class="fa-solid <?php echo $icon; ?>"></i>
                            </div>
                            <div class="competition-content">
                                <div class="competition-title">
                                    <h2><?php echo htmlspecialchars($c['title']); ?></h2>
                                    <span class="status <?php echo htmlspecialchars($c['status']); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($c['description'] ?? ''); ?></p>
                                <div class="competition-meta">
                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        <?php echo htmlspecialchars($deadlineLabel); ?>
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-users"></i>
                                        <?php echo $participantsLabel; ?> Participants
                                    </span>
                                    <?php if ($c['venue']): ?>
                                    <span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?php echo htmlspecialchars($c['venue']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- right -->
                        <div class="competition-action">
                            <?php if (!$c['is_registered'] && $registrationClosed): ?>
                                <button class="participate-btn" type="button" disabled style="opacity:0.55; cursor:not-allowed;">
                                    <?php echo htmlspecialchars($buttonLabel); ?>
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                            <?php else: ?>
                                <form method="post" action="actions/register_competition.php">
                                    <input type="hidden" name="competition_id" value="<?php echo $c['competition_id']; ?>">
                                    <button class="participate-btn" type="submit">
                                        <?php echo htmlspecialchars($buttonLabel); ?>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div> 
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- competition list end -->
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
<script src="js/dashboard.js"></script>
<script src="js/search.js"></script>
<script src="js/competition.js"></script>
</body>
</html>