<?php
// ==========================================================
// Notifications helper
// ==========================================================
// Notifications are per-user (the `notifications` table already
// has user_id + is_read). Whenever a competition exists that this
// user hasn't been notified about yet, we create that notification
// row for them. This means: add a new competition -> every user
// picks up exactly one new unread notification for it next time
// they load a page.

function sync_competition_notifications(PDO $pdo, int $user_id): void
{
    $sql =
        "INSERT INTO notifications (user_id, competition_id, title, message, notification_type, is_read)
         SELECT ?, c.competition_id, c.title,
                CASE c.status
                    WHEN 'upcoming'  THEN 'New event is coming up. Check it out!'
                    WHEN 'active'    THEN 'Registration is now open.'
                    WHEN 'completed' THEN 'This competition has concluded.'
                    ELSE 'This competition was cancelled.'
                END,
                'competition', 0
         FROM competitions c
         WHERE NOT EXISTS (
             SELECT 1 FROM notifications n
             WHERE n.user_id = ? AND n.competition_id = c.competition_id
         )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id]);
}

function get_notifications(PDO $pdo, int $user_id, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . (int)$limit
    );
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_unread_notification_count(PDO $pdo, int $user_id): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetch()['c'];
}

function notification_time_ago($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M j, Y', strtotime($datetime));
}
