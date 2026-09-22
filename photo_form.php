<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photography Submission Form</title>
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/photo_form.css">
</head>
<body>
    <!-- heading for photo form -->
    <header class="top-header">
        <a href="create_post.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Categories
        </a>
        <h2>UIU Talent Hunt</h2>
        <!-- page title for photo form -->
    </header>
    <!-- upload form html start -->
     <div class="page-title">
        <div class="page-icon">
            <i class="fa-solid fa-camera"></i>
        </div>
        <h1>Photography Submission</h1>
        <p>
            Upload your photography and inspire the UIU community.
        </p>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#e74c3c; font-weight:600;"><?php echo htmlspecialchars(str_replace('_', ' ', $_GET['error'])); ?></p>
        <?php endif; ?>
     </div>
    <form class="upload-section" id="photoForm" method="post" action="actions/create_post.php" enctype="multipart/form-data">
        <input type="hidden" name="talent_type" value="photography">
        <input type="hidden" name="status" id="statusField" value="published">

        <!-- audio information card -->
        <div class="form-card">
            <h3>Photo Information</h3>
            <div class="form-group">
                <label for="photoTitle">Photo Title <span>*</span></label>
                <input type="text" id="photoTitle" name="title" placeholder="Enter your photo title">
            </div>
            <div class="form-group">
                <label for="photoCategory">Talent Category <span>*</span></label>
                <select name="category" id="photoCategory">
                    <option value=""hidden>Select Category</option>
                    <option value="Nature">Nature</option>
                    <option value="Portrait">Portrait</option>
                    <option value="Street Photography">Street Photography</option>
                    <option value="Wildlife">Wildlife</option>
                    <option value="Event Photography">Event Photography</option>
                    <option value="Artwork">Artwork</option>
                </select>
            </div>
            <div class="form-group">
                <label for="photoDescription">Description</label>
                <textarea name="description" id="photoDescription" rows="6" placeholder="Tell us something about your photography..."></textarea>
            </div>
        </div>
        <!--upload photo card  -->
        <div class="form-card">
            <h3>Upload Photo</h3>
            <div class="upload-box">
                <i class="fa-regular fa-image"></i>
                <h4>Drag & Drop Your Photo Here</h4>
                <p>or click the button below</p>
                <input type="file" id="photoFile" name="photo_file" accept=".jpg,.jpeg,.png,.webp" hidden>
                <button type="button" class="upload-btn" id="choosePhotobtn">Choose Photo</button>
                <p class="selected-photo" id="selectedPhoto"></p>
                <div class="upload-info">
                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Accepted Formats:
                        JPG, JPEG, PNG, WEBP
                    </span>
                    <span>
                        <i class="fa-solid fa-hard-drive"></i>
                        Maximum File Size:
                        10MB
                    </span>
                </div>
            </div>
        </div>
        <!-- Detected photo information -->
        <div class="form-card">
            <h3>Detected File Information</h3>
            <div class="photo-info-grid">
                <div class="info-item">
                    <span>File Name</span>
                    <strong id="fileName">Waiting for upload...</strong>
                </div>
                <div class="info-item">
                    <span>Format</span>
                    <strong id="fileFormat">---</strong>
                </div>
                <div class="info-item">
                    <span>Resolution</span>
                    <strong id="fileResolution">---</strong>
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


        <!-- preview card -->
        <div class="form-card">
            <h3>Photo Preview</h3>
            <div class="photo-preview">
                <img id="photoPreview" src="" alt="Photo Preview">
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
                    <span>Photos containing copyrighted material may be removed.</span>
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
                        Publish Photo
                    </button>
                </div>
            </div>
        </div>

    </form>
    <!-- upload form html end  -->
<script src="js/photo_form.js"></script>     
</body>
</html>