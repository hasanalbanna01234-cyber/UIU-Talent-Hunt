<?php
// ==========================================================
// Shared post-rendering helpers
// ==========================================================
// Used by dashboard.php and myprofile.php so both feeds render
// media (video/audio/image) and blog previews the same way,
// straight from post_media — no duplicated logic, no fake data.

// Pick out the first video / audio / image row for a post from
// the already-fetched $mediaByPost map (post_id => [rows]).
function post_primary_media($postId, $mediaByPost)
{
    $video = $audio = $image = null;
    foreach ($mediaByPost[$postId] ?? [] as $m) {
        if ($m['media_type'] === 'video' && !$video) $video = $m;
        if ($m['media_type'] === 'audio' && !$audio) $audio = $m;
        if ($m['media_type'] === 'image' && !$image) $image = $m;
    }
    return ['video' => $video, 'audio' => $audio, 'image' => $image];
}

// Renders the actual playable media block for a post: real <video>/<audio>
// with controls, or the cover image — always from post_media, never a
// hard-coded filename. Falls back to a generic placeholder image only
// when a post genuinely has no media at all.
function render_post_media($post, $mediaByPost, $fallbackImage = 'images/post1.jpg')
{
    $media = post_primary_media($post['post_id'], $mediaByPost);
    $type  = $post['talent_type'];

    ob_start();

    if ($type === 'video' && $media['video']) {
        $src   = htmlspecialchars($media['video']['file_path']);
        $mime  = htmlspecialchars($media['video']['mime_type'] ?: 'video/mp4');
        $poster = $media['image'] ? ' poster="' . htmlspecialchars($media['image']['file_path']) . '"' : '';
        echo '<video class="feed-video" controls preload="metadata"' . $poster . '>';
        echo '<source src="' . $src . '" type="' . $mime . '">';
        echo 'Your browser does not support the video tag.';
        echo '</video>';
    } elseif ($type === 'audio') {
        if ($media['image']) {
            echo '<img class="feed-cover-img" src="' . htmlspecialchars($media['image']['file_path']) . '" alt="">';
        }
        if ($media['audio']) {
            $src  = htmlspecialchars($media['audio']['file_path']);
            $mime = htmlspecialchars($media['audio']['mime_type'] ?: 'audio/mpeg');
            echo '<audio class="feed-audio" controls preload="metadata">';
            echo '<source src="' . $src . '" type="' . $mime . '">';
            echo 'Your browser does not support the audio tag.';
            echo '</audio>';
        }
    } elseif ($type === 'photography') {
        $src = $media['image'] ? htmlspecialchars($media['image']['file_path']) : $fallbackImage;
        echo '<img class="feed-cover-img" src="' . $src . '" alt="">';
    } elseif ($type === 'blog') {
        if ($media['image']) {
            echo '<img class="feed-cover-img" src="' . htmlspecialchars($media['image']['file_path']) . '" alt="">';
        }
    } else {
        echo '<img class="feed-cover-img" src="' . $fallbackImage . '" alt="">';
    }

    return ob_get_clean();
}

// Just the cover/thumbnail image for a post (used for small preview
// tiles where a full player would be too heavy) — first image media
// row, or a generic fallback if the post has none.
function post_cover_image($postId, $mediaByPost, $fallback = 'images/post1.jpg')
{
    foreach ($mediaByPost[$postId] ?? [] as $m) {
        if ($m['media_type'] === 'image') return htmlspecialchars($m['file_path']);
    }
    return $fallback;
}

// Blog content block: a clamped preview with a "See more/less" toggle
// (JS-driven — see feed_actions.js). Renders nothing if there is no
// content.
function render_blog_content($postId, $content)
{
    if (!$content) return '';
    $escaped = nl2br(htmlspecialchars($content));
    ob_start();
    ?>
    <div class="blog-content-preview" id="blog-content-<?php echo $postId; ?>"><?php echo $escaped; ?></div>
    <button type="button" class="blog-see-more-btn" data-target="blog-content-<?php echo $postId; ?>" style="display:none;">See more</button>
    <?php
    return ob_get_clean();
}

function talent_icon($type)
{
    return [
        'video' => 'fa-video',
        'audio' => 'fa-music',
        'photography' => 'fa-camera',
        'blog' => 'fa-pen-nib',
    ][$type] ?? 'fa-star';
}

function time_ago($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M j, Y', strtotime($datetime));
}
