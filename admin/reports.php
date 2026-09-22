<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

$pageTitle = 'Reports';
$pageSubtitle = 'Posts reported by students';
$activeNav = 'reports';

$reportsStmt = $pdo->query(
    "SELECT r.report_id, r.reason, r.status, r.created_at,
            p.post_id, p.title AS post_title,
            author.full_name AS author_name,
            reporter.full_name AS reporter_name
     FROM reports r
     JOIN posts p ON p.post_id = r.post_id
     JOIN users author ON author.user_id = p.user_id
     JOIN users reporter ON reporter.user_id = r.user_id
     ORDER BY r.created_at DESC"
);
$reports = $reportsStmt->fetchAll();

require_once 'includes/admin_header.php';
?>
            <div class="admin-panel">
                <h2>All Reports</h2>
                <?php if (empty($reports)): ?>
                    <p class="admin-empty">No posts have been reported yet.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Reported Post</th>
                                <th>Post Author</th>
                                <th>Reported By</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['post_title']); ?></td>
                                    <td><?php echo htmlspecialchars($r['author_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['reporter_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['reason']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($r['created_at'])); ?></td>
                                    <td>
                                        <form method="post" action="actions/update_report_status.php" style="display:flex; align-items:center; gap:8px;">
                                            <input type="hidden" name="report_id" value="<?php echo $r['report_id']; ?>">
                                            <select name="status" class="admin-status-select" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $r['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="reviewed" <?php echo $r['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                <option value="resolved" <?php echo $r['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
<?php require_once 'includes/admin_footer.php'; ?>
