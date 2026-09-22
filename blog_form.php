<?php require_once 'includes/session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Submission Form</title>
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/blog_form.css">
</head>
<body>
    <!-- heading for photo form -->
    <header class="top-header">
        <a href="create_post.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Categories
        </a>
        <h2>UIU Talent Hunt</h2>
        <!-- page title for blog form -->
    </header>
    <!-- upload form html start -->
     <div class="page-title">
        <div class="page-icon">
            <i class="fa-solid fa-pen-nib"></i>
        </div>
        <h1>Blog Submission</h1>
        <p>
            Share your ideas, stories, experiences, and creativity through writing with the UIU community.
        </p>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#e74c3c; font-weight:600;"><?php echo htmlspecialchars(str_replace('_', ' ', $_GET['error'])); ?></p>
        <?php endif; ?>
     </div>
    <form class="upload-section" id="blogForm" method="post" action="actions/create_post.php" enctype="multipart/form-data">
        <input type="hidden" name="talent_type" value="blog">
        <input type="hidden" name="status" id="statusField" value="published">

        <!-- blog information card -->
        <div class="form-card">
            <h3>Blog Information</h3>
            <div class="form-group">
                <label for="blogTitle">Blog Title <span>*</span></label>
                <input type="text" id="blogTitle" name="title" placeholder="Enter your blog title">
            </div>
            <div class="form-group">
                <label for="blogCategory">Talent Category <span>*</span></label>
                <select name="category" id="blogCategory">
                    <option value=""hidden>Select Category</option>
                    <option value="Technology">Technology</option>
                    <option value="Education">Education</option>
                    <option value="Motivational">Motivational</option>
                    <option value="Lifestyle">Lifestyle</option>
                    <option value="Story">Story</option>
                    <option value="Opinion">Opinion</option>
                    <option value="Campus Life">Campus Life</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="blogContent">Blog Content <span>*</span></label>
                <textarea name="content" id="blogContent" rows="15" placeholder="Start writing here..."></textarea>
            </div>
        </div>
        <!--upload cover image card  -->
        <div class="form-card">
            <h3>Upload Cover Image</h3>
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
                        5MB
                    </span>
                </div>
            </div>
        </div>

        <!-- preview card -->

        <div class="form-card">
            <h3>Cover Image Preview</h3>
            <div class="photo-preview">
                <img id="photoPreview" src="" alt="Photo Preview">
            </div>

        </div>

        <!-- Detected file information -->
        <div class="form-card">
            <h3>Blog Statistics</h3>
            <div class="blog-info-grid">
                <div class="info-item">
                    <span>Word Count</span>
                    <strong id="wc">---</strong>
                </div>
                <div class="info-item">
                    <span>Character Count</span>
                    <strong id="cc">---</strong>
                </div>
                <div class="info-item status-card">
                    <span>Status</span>
                    <strong id="fileStatus">Waiting for upload</strong>
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
                    <span>Blogs containing plagiarized or copyrighted content may be removed.</span>
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
                        Publish Blog
                    </button>
                </div>
            </div>
        </div>

    </form>
    <!-- upload form html end  -->
<script src="js/blog_form.js"></script>     
</body>
</html>