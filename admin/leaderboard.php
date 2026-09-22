<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../includes/leaderboard_helper.php';

$pageTitle = 'Leaderboard';
$pageSubtitle = 'Read-only view of the current student rankings';
$activeNav = 'leaderboard';

// Reuses the exact same scoring function as the student-facing leaderboard —
// no separate leaderboard table or duplicated calculation logic.
$rankedUsers = get_leaderboard($pdo);

require_once 'includes/admin_header.php';
?>
            <div class="admin-panel">
                <h2>Student Rankings</h2>
                <?php if (empty($rankedUsers)): ?>
                    <p class="admin-empty">No ranked students yet.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student</th>
                                <th>Department</th>
                                <th>Published Posts</th>
                                <th>Top Category</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rankedUsers as $idx => $u): ?>
                                <tr>
                                    <td>#<?php echo $idx + 1; ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['department_code']); ?></td>
                                    <td><?php echo (int)$u['post_count']; ?></td>
                                    <td><?php echo $u['top_category'] ? htmlspecialchars(ucfirst($u['top_category'])) : '—'; ?></td>
                                    <td><?php echo number_format($u['score']); ?> pts</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
<?php require_once 'includes/admin_footer.php'; ?>
