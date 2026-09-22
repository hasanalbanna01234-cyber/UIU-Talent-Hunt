<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

$pageTitle = 'Dashboard';
$pageSubtitle = 'Overview of UIU Talent Hunt activity';
$activeNav = 'dashboard';

$stats = [
    'students'     => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
    'posts'        => (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'drafts'       => (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn(),
    'likes'        => (int)$pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn(),
    'comments'     => (int)$pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'active'")->fetchColumn(),
    'shares'       => (int)$pdo->query("SELECT COUNT(*) FROM shares")->fetchColumn(),
    'competitions' => (int)$pdo->query("SELECT COUNT(*) FROM competitions")->fetchColumn(),
    'registrations'=> (int)$pdo->query("SELECT COUNT(*) FROM competition_registrations WHERE status = 'registered'")->fetchColumn(),
    'pending_reports' => (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
];

// A quick look at the most recently reported posts, for the dashboard glance
$recentReportsStmt = $pdo->query(
    "SELECT r.report_id, r.reason, r.status, r.created_at, p.title AS post_title
     FROM reports r
     JOIN posts p ON p.post_id = r.post_id
     ORDER BY r.created_at DESC
     LIMIT 5"
);
$recentReports = $recentReportsStmt->fetchAll();

require_once 'includes/admin_header.php';
?>
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['students']); ?></h3>
                        <p>Active Students</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['posts']); ?></h3>
                        <p>Published Posts</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-heart"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['likes']); ?></h3>
                        <p>Total Likes</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-comment"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['comments']); ?></h3>
                        <p>Total Comments</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-share-nodes"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['shares']); ?></h3>
                        <p>Total Shares</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-trophy"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['competitions']); ?></h3>
                        <p>Competitions</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['registrations']); ?></h3>
                        <p>Competition Registrations</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon"><i class="fa-solid fa-flag"></i></div>
                    <div>
                        <h3><?php echo number_format($stats['pending_reports']); ?></h3>
                        <p>Pending Reports</p>
                    </div>
                </div>
            </div>

            <div class="admin-panel">
                <h2>Recently Reported Posts</h2>
                <?php if (empty($recentReports)): ?>
                    <p class="admin-empty">No reports have been submitted yet.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Post</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Reported</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['post_title']); ?></td>
                                    <td><?php echo htmlspecialchars($r['reason']); ?></td>
                                    <td><span class="admin-badge <?php echo htmlspecialchars($r['status']); ?>"><?php echo htmlspecialchars(ucfirst($r['status'])); ?></span></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($r['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
<?php require_once 'includes/admin_footer.php'; ?>
