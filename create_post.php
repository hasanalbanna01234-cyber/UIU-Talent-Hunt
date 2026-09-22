<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | Create Post</title>
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/create_post.css">
</head>
<body>
    <!-- header part html start -->
    <header class="top-header">
        <a href="dashboard.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Dashboard
        </a>
        <h2>UIU Talent Hunt</h2>
    </header>
    <!-- header part html end -->
    <!-- Page title -->
    <div class="page-title">
        <h1>Choose Your Creative Category</h1>
        <p>
            Select what you'd like to showcase and start sharing your talent.
        </p>
    </div>
    <!-- content of create post html start-->
    <div class="container">
        <div class="type-card" id="videoCard">
            <i class="fa-solid fa-video video-icon"></i>
            <h3>Video</h3>
            <p>Upload performances, presentations, short films and creative videos.</p>
        </div>
        <div class="type-card" id="audioCard">
            <i class="fa-solid fa-music audio-icon"></i>
            <h3>Audio</h3>
            <p>Share songs, podcasts, speeches and voice recordings.</p>
        </div>
        <div class="type-card" id="photoCard">
            <i class="fa-solid fa-camera photo-icon"></i>
            <h3>Photography</h3>
            <p>Upload photographs, digital art, paintings, sketches and other visual creations.</p>
        </div>
        <div class="type-card" id="blogCard">
            <i class="fa-solid fa-pen-nib blog-icon"></i>
            <h3>Blog</h3>
            <p>Publish articles, poems, stories and written content.</p>
        </div>
    </div>
    <!-- content of create post html end -->
    
<script src="js/create_post.js"></script>    
</body>
</html>