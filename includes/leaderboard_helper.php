<?php
// ==========================================================
// Leaderboard scoring helper
// ==========================================================
// Score formula (developer-defined, as required by the spec):
//   10 points per like, 15 points per comment, 20 points per share,
//   summed across all of a user's published posts.
// Only OTHER users' engagement counts — a post owner liking,
// commenting on, or sharing their own post never earns points
// (likes/shares from the owner are blocked outright at the
// database layer; the comment exclusion below is the safety net
// since owners are still allowed to comment on their own posts).
//
// $category and $timeRange let callers (the Leaderboard page's
// filters) narrow the scoring to a single talent type and/or a
// date range, while every other caller (dashboard sidebar, my
// profile achievements, etc.) can keep calling this with just
// $limit and get the all-time, all-category ranking as before.

function get_leaderboard(PDO $pdo, ?int $limit = null, string $category = 'all', string $timeRange = 'all_time'): array
{
    $allowedCategories = ['all', 'video', 'audio', 'photography', 'blog'];
    $allowedTimeRanges = ['this_month', 'last_month', 'this_year', 'all_time'];

    if (!in_array($category, $allowedCategories, true)) {
        $category = 'all';
    }
    if (!in_array($timeRange, $allowedTimeRanges, true)) {
        $timeRange = 'all_time';
    }

    // Only ever built from the whitelists above — never from raw user
    // input — so it's safe to embed directly into the SQL below.
    $categoryCondition = function (string $alias) use ($category): string {
        return $category !== 'all' ? "AND {$alias}.talent_type = '{$category}'" : "";
    };

    $timeCondition = function (string $alias) use ($timeRange): string {
        switch ($timeRange) {
            case 'this_month':
                return "AND YEAR({$alias}.created_at) = YEAR(CURDATE()) AND MONTH({$alias}.created_at) = MONTH(CURDATE())";
            case 'last_month':
                return "AND YEAR({$alias}.created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH({$alias}.created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
            case 'this_year':
                return "AND YEAR({$alias}.created_at) = YEAR(CURDATE())";
            default: // all_time
                return "";
        }
    };

    $postCond = $categoryCondition('p') . $timeCondition('p');
    $likeCond = $timeCondition('l');
    $commentCond = $timeCondition('c');
    $shareCond = $timeCondition('s');

    $topCategoryCond = $categoryCondition('p3') . $timeCondition('p3');

    $sql =
        "SELECT u.user_id, u.full_name, u.profile_image, d.department_code,
                SUM(
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id AND l.user_id <> p.user_id {$likeCond}) * 10 +
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active' AND c.user_id <> p.user_id {$commentCond}) * 15 +
                    (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id AND s.user_id <> p.user_id {$shareCond}) * 20
                ) AS score,
                COUNT(p.post_id) AS post_count,
                (
                    SELECT p3.talent_type
                    FROM posts p3
                    WHERE p3.user_id = u.user_id AND p3.status = 'published' {$topCategoryCond}
                    GROUP BY p3.talent_type
                    ORDER BY COUNT(*) DESC
                    LIMIT 1
                ) AS top_category
         FROM users u
         JOIN posts p ON p.user_id = u.user_id AND p.status = 'published' {$postCond}
         JOIN departments d ON d.department_id = u.department_id
         WHERE u.status = 'active'
         GROUP BY u.user_id, u.full_name, u.profile_image, d.department_code
         HAVING score > 0
         ORDER BY score DESC, post_count DESC, u.full_name ASC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }

    return $pdo->query($sql)->fetchAll();
}

// ----------------------------------------------------------
// Per-category scoring for ONE user — used to work out which
// talent category (if any) a user is genuinely strong in, for
// the "Top Photographer / Top Writer / ..." achievement.
// Returns e.g. ['video' => 120, 'audio' => 0, 'photography' => 340, 'blog' => 0]
// ----------------------------------------------------------
function get_user_category_scores(PDO $pdo, int $user_id): array
{
    $categories = ['video', 'audio', 'photography', 'blog'];
    $scores = [];

    foreach ($categories as $cat) {
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id AND l.user_id != p.user_id) * 10 +
                (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active' AND c.user_id != p.user_id) * 15 +
                (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id AND s.user_id != p.user_id) * 20
            ), 0) AS score
            FROM posts p
            WHERE p.user_id = ? AND p.status = 'published' AND p.talent_type = ?"
        );
        $stmt->execute([$user_id, $cat]);
        $scores[$cat] = (int)$stmt->fetch()['score'];
    }

    return $scores;
}

// ----------------------------------------------------------
// Rank of ONE user within a single talent category, based on
// the same per-category scoring used above. Returns null if the
// user has no published posts in that category.
// ----------------------------------------------------------
function get_user_category_rank(PDO $pdo, int $user_id, string $category): ?int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM posts WHERE user_id = ? AND status = 'published' AND talent_type = ?");
    $stmt->execute([$user_id, $category]);
    if ((int)$stmt->fetch()['c'] === 0) {
        return null;
    }

    $sql =
        "SELECT u.user_id,
                COALESCE(SUM(
                    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id AND l.user_id != p.user_id) * 10 +
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active' AND c.user_id != p.user_id) * 15 +
                    (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id AND s.user_id != p.user_id) * 20
                ), 0) AS score
         FROM users u
         JOIN posts p ON p.user_id = u.user_id AND p.status = 'published' AND p.talent_type = ?
         WHERE u.status = 'active'
         GROUP BY u.user_id
         ORDER BY score DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $idx => $row) {
        if ((int)$row['user_id'] === $user_id) {
            return $idx + 1;
        }
    }

    return null;
}

// ----------------------------------------------------------
// Dynamic, data-based achievements for My Profile.
// Only three kinds, matching what's actually earned:
//   1) A talent/category achievement ("Top Photographer" etc.) —
//      only when the user is genuinely top-3 in that category.
//   2) Engagement milestones (likes / shares received from OTHER
//      users) — only the highest tier reached is shown.
//   3) A leaderboard achievement ("Top 5 Creator") — only when
//      the user is actually ranked in the top 5 overall with a
//      score greater than zero.
// $overallRank/$overallScore are passed in so the caller (which
// already computed the full ranking for the stats box) doesn't
// have to run that query twice.
// ----------------------------------------------------------
function get_user_achievements(PDO $pdo, int $user_id, ?int $overallRank, int $overallScore): array
{
    $achievements = [];

    // --- Talent / category achievement ---
    $categoryScores = get_user_category_scores($pdo, $user_id);
    arsort($categoryScores);
    $topCategory = array_key_first($categoryScores);

    if ($topCategory !== null && $categoryScores[$topCategory] > 0) {
        $rank = get_user_category_rank($pdo, $user_id, $topCategory);

        if ($rank !== null && $rank <= 3) {
            $titles = [
                'video' => 'Top Videographer',
                'audio' => 'Top Singer',
                'photography' => 'Top Photographer',
                'blog' => 'Top Writer',
            ];
            $icons = [
                'video' => 'fa-video',
                'audio' => 'fa-music',
                'photography' => 'fa-camera',
                'blog' => 'fa-pen-nib',
            ];
            $labels = [
                'video' => 'Video',
                'audio' => 'Audio',
                'photography' => 'Photography',
                'blog' => 'Blog',
            ];
            $achievements[] = [
                'icon' => $icons[$topCategory],
                'title' => $titles[$topCategory],
                'subtitle' => $labels[$topCategory],
            ];
        }
    }

    // --- Genuine engagement totals (the post owner's own likes/shares
    //     can never exist thanks to the block at insert time, but the
    //     join still explicitly excludes them as a safety net) ---
    $likeStmt = $pdo->prepare(
        "SELECT COUNT(*) AS c FROM likes l
         JOIN posts p ON p.post_id = l.post_id
         WHERE p.user_id = ? AND p.status = 'published' AND l.user_id != p.user_id"
    );
    $likeStmt->execute([$user_id]);
    $totalLikes = (int)$likeStmt->fetch()['c'];

    $shareStmt = $pdo->prepare(
        "SELECT COUNT(*) AS c FROM shares s
         JOIN posts p ON p.post_id = s.post_id
         WHERE p.user_id = ? AND p.status = 'published' AND s.user_id != p.user_id"
    );
    $shareStmt->execute([$user_id]);
    $totalShares = (int)$shareStmt->fetch()['c'];

    $likeMilestone = achievement_milestone_label($totalLikes, [100, 500, 1000, 5000, 10000]);
    if ($likeMilestone !== null) {
        $achievements[] = ['icon' => 'fa-heart', 'title' => $likeMilestone . ' Likes', 'subtitle' => 'Community Achievement'];
    }

    $shareMilestone = achievement_milestone_label($totalShares, [100, 500, 1000]);
    if ($shareMilestone !== null) {
        $achievements[] = ['icon' => 'fa-share-nodes', 'title' => $shareMilestone . ' Shares', 'subtitle' => 'Community Achievement'];
    }

    // --- Leaderboard achievement ---
    if ($overallRank !== null && $overallRank <= 5 && $overallScore > 0) {
        $achievements[] = ['icon' => 'fa-star', 'title' => 'Top 5 Creator', 'subtitle' => 'Leaderboard'];
    }

    return $achievements;
}

// Returns the display label ("1K", "500", ...) for the HIGHEST tier
// reached, or null if the count hasn't reached even the first tier.
function achievement_milestone_label(int $count, array $tiers): ?string
{
    $reached = null;
    foreach ($tiers as $t) {
        if ($count >= $t) {
            $reached = $t;
        }
    }
    if ($reached === null) {
        return null;
    }
    return $reached >= 1000 ? (($reached / 1000) . 'K') : (string)$reached;
}
