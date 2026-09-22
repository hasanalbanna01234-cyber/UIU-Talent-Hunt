<?php
// Expects $pageTitle, $pageSubtitle, and $activeNav ('dashboard'|'competitions'|'reports'|'leaderboard')
// to be set by the including page before this partial is required.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin'); ?> | UIU Talent Hunt Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=20260920">
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <img src="../images/logo.png" alt="UIU Talent Hunt">
                <span>ADMIN PANEL</span>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="<?php echo $activeNav === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
                <a href="competitions.php" class="<?php echo $activeNav === 'competitions' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-trophy"></i> Competitions
                </a>
                <a href="reports.php" class="<?php echo $activeNav === 'reports' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-flag"></i> Reports
                </a>
                <a href="leaderboard.php" class="<?php echo $activeNav === 'leaderboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-ranking-star"></i> Leaderboard
                </a>
                <a href="logout.php" class="admin-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-topbar">
                <div>
                    <h1><?php echo htmlspecialchars($pageTitle ?? ''); ?></h1>
                    <?php if (!empty($pageSubtitle)): ?>
                        <p><?php echo htmlspecialchars($pageSubtitle); ?></p>
                    <?php endif; ?>
                </div>
            </div>
