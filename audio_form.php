<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Submission Form</title>
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/audio_form.css">
</head>
<body>
    <!-- heading for audio form -->
    <header class="top-header">
        <a href="create_post.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Categories
        </a>
        <h2>UIU Talent Hunt</h2>
        <!-- page title for audio form -->
    </header>
    <!-- upload form html start -->
     <div class="page-title">
        <div class="page-icon">
            <i class="fa-solid fa-music"></i>
        </div>
        <h1>Audio Submission</h1>
        <p>
            Share your voice, music, or podcast with the UIU community and let your creativity be heard.
        </p>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#e74c3c; font-weight:600;"><?php echo htmlspecialchars(str_replace('_', ' ', $_GET['error'])); ?></p>
        <?php endif; ?>
     </div>
    <form class="upload-section" id="audioForm" method="post" action="actions/create_post.php" enctype="multipart/form-data">
        <input type="hidden" name="talent_type" value="audio">
        <input type="hidden" name="status" id="statusField" value="published">

        <!-- audio information card -->
        <div class="form-card">
            <h3>Audio Information</h3>
            <div class="form-group">
                <label for="audioTitle">Audio Title <span>*</span></label>
                <input type="text" id="audioTitle" name="title" placeholder="Enter your audio title">
            </div>
            <div class="form-group">
                <label for="audioCategory">Talent Category <span>*</span></label>
                <select name="category" id="audioCategory">
                    <option value=""hidden>Select Category</option>
                    <option value="Singing">Singing</option>
                    <option value="Instrumental">Instrumental</option>
                    <option value="Podcast">Podcast</option>
                    <option value="Recitation">Recitation</option>
                    <option value="Speech">Speech</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="audioDescription">Description</label>
                <textarea name="description" id="audioDescription" rows="6" placeholder="Tell us something about your performance..."></textarea>
            </div>
        </div>
        <!--upload audio card  -->
        <div class="form-card">
            <h3>Upload Audio</h3>
            <div class="upload-box">
                <i class="fa-solid fa-file-audio"></i>
                <h4>Drag & Drop Your Audio Here</h4>
                <p>or click the button below</p>
                <input type="file" id="audioFile" name="audio_file" accept=".mp3,.wav,.aac,.flac" hidden>
                <button type="button" class="upload-btn" id="chooseAudiobtn">Choose Audio</button>
                <p class="selected-audio" id="selectedAudio"></p>
                <div class="upload-info">
                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Accepted Formats:
                        MP3, WAV, AAC, FLAC
                    </span>
                    <span>
                        <i class="fa-solid fa-hard-drive"></i>
                        Maximum File Size:
                        100MB
                    </span>
                </div>
            </div>
        </div>
        <!-- Detected audio information -->
        <div class="form-card">
            <h3>Detected File Information</h3>
            <div class="audio-info-grid">
                <div class="info-item">
                    <span>File Name</span>
                    <strong id="fileName">Waiting for upload...</strong>
                </div>
                <div class="info-item">
                    <span>Format</span>
                    <strong id="fileFormat">---</strong>
                </div>
                <div class="info-item">
                    <span>Bitrate</span>
                    <strong id="fileBitrate">---</strong>
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
                <p>Choose a cover image for your audio.</p>
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
                    <span>Audios containing copyrighted material may be removed.</span>
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
                        Publish Audio
                    </button>
                </div>
            </div>
        </div>

    </form>
    <!-- upload form html end  -->
<script src="js/audio_form.js"></script>     
</body>
</html>