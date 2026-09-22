<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

$pageTitle = 'Competitions';
$pageSubtitle = 'Manage UIU Talent Hunt competitions';
$activeNav = 'competitions';

$formErrors = $_SESSION['admin_form_errors'] ?? [];
$oldInput = $_SESSION['admin_form_old'] ?? [];
unset($_SESSION['admin_form_errors'], $_SESSION['admin_form_old']);

$addedFlag = $_GET['added'] ?? null;

$compStmt = $pdo->query(
    "SELECT c.*,
            (SELECT COUNT(*) FROM competition_registrations r WHERE r.competition_id = c.competition_id AND r.status = 'registered') AS participant_count
     FROM competitions c
     ORDER BY c.event_date DESC"
);
$competitions = $compStmt->fetchAll();

require_once 'includes/admin_header.php';
?>
            <?php if ($addedFlag === '1'): ?>
                <div class="admin-alert success">Competition added successfully.</div>
            <?php elseif ($addedFlag === '0' && !empty($formErrors)): ?>
                <div class="admin-alert error">
                    <?php foreach ($formErrors as $e) echo htmlspecialchars($e) . '<br>'; ?>
                </div>
            <?php endif; ?>

            <div class="admin-panel">
                <h2>Add Competition</h2>
                <form method="post" action="actions/add_competition.php">
                    <div class="admin-form-grid">
                        <div class="admin-form-group full">
                            <label for="title">Competition Name</label>
                            <input type="text" id="title" name="title" maxlength="150" required
                                   value="<?php echo htmlspecialchars($oldInput['title'] ?? ''); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="event_date">Date</label>
                            <input type="date" id="event_date" name="event_date" required
                                   value="<?php echo htmlspecialchars($oldInput['event_date'] ?? ''); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="event_time">Time</label>
                            <input type="time" id="event_time" name="event_time"
                                   value="<?php echo htmlspecialchars($oldInput['event_time'] ?? ''); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="venue">Place</label>
                            <input type="text" id="venue" name="venue" maxlength="200"
                                   value="<?php echo htmlspecialchars($oldInput['venue'] ?? ''); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="registration_deadline">Registration Deadline</label>
                            <input type="date" id="registration_deadline" name="registration_deadline"
                                   value="<?php echo htmlspecialchars($oldInput['registration_deadline'] ?? ''); ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="max_participants">Maximum Participants</label>
                            <input type="number" id="max_participants" name="max_participants" min="1" placeholder="Leave blank for no limit"
                                   value="<?php echo htmlspecialchars($oldInput['max_participants'] ?? ''); ?>">
                        </div>
                        <div class="admin-form-group full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($oldInput['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="admin-submit-btn">
                        <i class="fa-solid fa-plus"></i> Add Competition
                    </button>
                </form>
            </div>

            <div class="admin-panel">
                <h2>All Competitions</h2>
                <?php if (empty($competitions)): ?>
                    <p class="admin-empty">No competitions yet.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Deadline</th>
                                <th>Place</th>
                                <th>Participants</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($competitions as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($c['event_date'])); ?></td>
                                    <td><?php echo $c['registration_deadline'] ? date('M j, Y', strtotime($c['registration_deadline'])) : '—'; ?></td>
                                    <td><?php echo htmlspecialchars($c['venue'] ?? '—'); ?></td>
                                    <td>
                                        <?php echo (int)$c['participant_count']; ?><?php echo $c['max_participants'] ? ' / ' . (int)$c['max_participants'] : ''; ?>
                                    </td>
                                    <td><span class="admin-badge <?php echo htmlspecialchars($c['status']); ?>"><?php echo htmlspecialchars(ucfirst($c['status'])); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
<?php require_once 'includes/admin_footer.php'; ?>
