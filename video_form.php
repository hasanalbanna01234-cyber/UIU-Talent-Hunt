<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Submission Form</title>
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/video_form.css">
</head>
<body>
    <!-- heading for video form -->
    <header class="top-header">
        <a href="create_post.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Categories
        </a>
        <h2>UIU Talent Hunt</h2>
        <!-- page title for video form -->
    </header>
    <!-- upload form html start -->
     <div class="page-title">
        <div class="page-icon">
            <i class="fa-solid fa-video"></i>
        </div>
        <h1>Video Submission</h1>
        <p>
            Showcase your talent through video and inspire the UIU community with your creativity.
        </p>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#e74c3c; font-weight:600;"><?php echo htmlspecialchars(str_replace('_', ' ', $_GET['error'])); ?></p>
        <?php endif; ?>
     </div>
    <form class="upload-section" id="videoForm" method="post" action="actions/create_post.php" enctype="multipart/form-data">
        <input type="hidden" name="talent_type" value="video">
        <input type="hidden" name="status" id="statusField" value="published">


        <!-- video information card -->
        <div class="form-card">
            <h3>Video Information</h3>
            <div class="form-group">
                <label for="videoTitle">Video Title <span>*</span></label>
                <input type="text" id="videoTitle" name="title" placeholder="Enter your video title">
            </div>
            <div class="form-group">
                <label for="videoCategory">Talent Category <span>*</span></label>
                <select name="category" id="videoCategory">
                    <option value=""hidden>Select Category</option>
                    <option value="Dance">Dance</option>
                    <option value="Singing">Singing</option>
                    <option value="Instrumental">Instrumental</option>
                    <option value="Artwork">Artwork</option>
                    <option value="Recitation">Recitation</option>
                    <option value="Drama">Drama</option>
                    <option value="Short Film">Short Film</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="videoDescription">Description</label>
                <textarea name="description" id="videoDescription" rows="6" placeholder="Tell us something about your performance..."></textarea>
            </div>
        </div>
        <!--upload video card  -->
        <div class="form-card">
            <h3>Upload Video</h3>
            <div class="upload-box">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <h4>Drag & Drop Your Video Here</h4>
                <p>or click the button below</p>
                <input type="file" id="videoFile" name="video_file" accept=".mp4,.mov,.avi,.mkv,.webm" hidden>
                <button type="button" class="upload-btn" id="chooseVideobtn">Choose Video</button>
                <p class="selected-video" id="selectedVideo">No video selected</p>
                <div class="upload-info">
                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Accepted Formats:
                        MP4, MOV, AVI, MKV, WEBM
                    </span>
                    <span>
                        <i class="fa-solid fa-hard-drive"></i>
                        Maximum File Size:
                        100MB
                    </span>
                </div>
            </div>
        </div>
        <!-- Detected video information -->
        <div class="form-card">
            <h3>Detected Video Information</h3>
            <div class="video-info-grid">
                <div class="info-item">
                    <span>File Name</span>
                    <strong id="fileName">Waiting for upload...</strong>
                </div>
                <div class="info-item">
                    <span>Format</span>
                    <strong id="fileFormat">---</strong>
                </div>
                <div class="info-item">
                    <span>Quality</span>
                    <strong id="fileQuality">---</strong>
                </div>
                <div class="info-item">
                    <span>Duration</span>
                    <strong id="fileDuration">---</strong>
                </div>

                <div class="info-item">
                    <span>File Size</span>
                    <strong id="fileSize">---</strong>
                </div>

                <div class="info-item status-card">
                    <span>Status</span>
                    <strong id="fileStatus">Waiting for upload</strong>
                </div>

            </div>
        </div>
        
        <!-- thumbnail upload -->
        <div class="form-card">
            <h3>Upload Thumbnail</h3>
            <div class="thumbnail-box">
                <i class="fa-regular fa-image"></i>
                <h4>Upload Thumbnail</h4>
                <p>Choose a thumbnail for your video.</p>
                <input type="file" id="thumbnailfile" name="thumbnail_file" accept=".jpg,.jpeg,.png" hidden>
                <button type="button" class="upload-btn" id="chooseThumbBtn">Choose Image</button>
                <div class="thumbnail-preview">
                    <img id="thumbnailPreview" src="" alt="">
                </div>
                <div class="upload-info">
                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        JPG • JPEG • PNG
                    </span>
                    <span>
                        <i class="fa-solid fa-hard-drive"></i>
                        Maximum Size : 5MB
                    </span>
                </div>
            </div>
        </div> 
        <!--publish card  -->
        <div class="form-card">
            <h3>Before Publishing</h3>
            <div class="guidelines">
                <div class="guide-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Make sure your content follows the UIU Talent Hunt community guidelines.</span>
                </div>
                <div class="guide-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Only upload original content created by you.</span>
                </div>
                <div class="guide-item">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Videos containing copyrighted material may be removed.</span>
                </div>
                <div class="publish-options">
                    <label for="" class="checkbox-container">
                        <input type="checkbox" id="agree">
                        <span class="checkmark"></span>
                        I confirm that this content belongs to me and follows all community guidelines.
                    </label>
                </div>
                <div class="publish-buttons">
                    <button class="draft-btn" type="button">
                        Save as Draft
                    </button>
                    <button class="publish-btn" type="button">
                        <i class="fa-solid fa-paper-plane"></i>
                        Publish Video
                    </button>
                </div>
            </div>
        </div>

    </form>
    <!-- upload form html end  -->
<script src="js/video_form.js"></script>     
</body>
</html>