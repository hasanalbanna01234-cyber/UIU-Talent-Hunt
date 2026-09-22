<?php
// ==========================================================
// Inline comment section for a single post
// ==========================================================
// Expects $pid (int post_id) to already be set by the including
// file. Hidden by default (see .comment-section in dashboard.css);
// toggled open by clicking the post's Comment button (feed_actions.js).
// Comments themselves are loaded/posted via AJAX (get_comments.php /
// comment.php) — nothing here is pre-rendered from PHP so the list
// always reflects the live database state.
?>
<div class="comment-section" id="comment-section-<?php echo $pid; ?>" data-post-id="<?php echo $pid; ?>">
    <div class="comment-list" id="comment-list-<?php echo $pid; ?>"></div>
    <div class="comment-input-row">
        <textarea class="comment-textarea" rows="1" placeholder="Write a comment..."></textarea>
        <button type="button" class="comment-submit-btn" title="Post comment">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>
