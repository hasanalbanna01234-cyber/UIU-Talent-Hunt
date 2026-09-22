// ==========================================================
// Discover Feed - "View All" pagination (show remaining posts
// already rendered in the page, no reload / no separate page)
// ==========================================================
(function () {
    const viewAllBtn = document.getElementById("viewAllPostsBtn");
    const extraPosts = document.getElementById("extraFeedPosts");

    if (viewAllBtn && extraPosts) {
        viewAllBtn.addEventListener("click", function () {
            extraPosts.classList.add("active");
            viewAllBtn.closest(".view-all-feed-wrapper").style.display = "none";
        });
    }
})();

// ==========================================================
// Feed Engagement: Like / Comment / Share (AJAX, no alerts)
// ==========================================================

function postForm(url, data) {
    const params = new URLSearchParams(data);
    return fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString(),
    }).then(function (res) {
        return res.json().then(function (data) {
            return { ok: res.ok, status: res.status, data: data };
        });
    });
}

function getJSON(url) {
    return fetch(url).then(function (res) {
        return res.json();
    });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
}

// ---------------- Inline toast (replaces browser alert()) ----------------
const inlineToast = document.getElementById("inlineToast");
let toastTimer = null;

function showToast(message) {
    if (!inlineToast) return;
    inlineToast.textContent = message;
    inlineToast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
        inlineToast.classList.remove("show");
    }, 2500);
}

// ==========================================================
// Like
// ==========================================================
document.querySelectorAll(".like-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
        if (btn.classList.contains("disabled-action")) {
            showToast("You can't like your own post.");
            return;
        }

        const postId = btn.getAttribute("data-post-id");

        postForm("actions/like.php", { post_id: postId })
            .then(function (res) {
                const data = res.data;
                if (data.error) {
                    showToast(data.error);
                    return;
                }
                const icon = btn.querySelector("i");
                const countEl = btn.querySelector(".like-count");

                if (data.liked) {
                    icon.classList.remove("fa-regular");
                    icon.classList.add("fa-solid");
                    btn.classList.add("liked");
                } else {
                    icon.classList.remove("fa-solid");
                    icon.classList.add("fa-regular");
                    btn.classList.remove("liked");
                }
                countEl.textContent = data.count;
            })
            .catch(function () {
                showToast("Could not update like right now.");
            });
    });
});

// ==========================================================
// Comment — inline, Facebook-style, opens under the post itself
// ==========================================================
function renderComments(listEl, comments) {
    if (!comments || comments.length === 0) {
        listEl.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
        return;
    }

    listEl.innerHTML = comments.map(function (c) {
        return (
            '<div class="comment-row">' +
                '<img src="' + c.profile_image + '" alt="">' +
                '<div class="comment-bubble">' +
                    '<h5>' + escapeHtml(c.full_name) + '</h5>' +
                    '<p>' + escapeHtml(c.comment_text) + '</p>' +
                    '<span>' + c.time_ago + '</span>' +
                '</div>' +
            '</div>'
        );
    }).join("");
}

function loadComments(postId, listEl) {
    listEl.innerHTML = '<p class="no-comments">Loading comments...</p>';
    getJSON("actions/get_comments.php?post_id=" + encodeURIComponent(postId))
        .then(function (data) {
            if (data.error) {
                listEl.innerHTML = '<p class="no-comments">Could not load comments.</p>';
                return;
            }
            renderComments(listEl, data.comments);
        })
        .catch(function () {
            listEl.innerHTML = '<p class="no-comments">Could not load comments.</p>';
        });
}

document.querySelectorAll(".comment-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
        const postId = btn.getAttribute("data-post-id");
        const section = document.getElementById("comment-section-" + postId);
        if (!section) return;

        const isOpening = !section.classList.contains("open");
        section.classList.toggle("open");

        if (isOpening) {
            const listEl = document.getElementById("comment-list-" + postId);
            loadComments(postId, listEl);
        }
    });
});

// Submit handler for every inline comment box on the page
document.querySelectorAll(".comment-section").forEach(function (section) {
    const postId = section.getAttribute("data-post-id");
    const textarea = section.querySelector(".comment-textarea");
    const submitBtn = section.querySelector(".comment-submit-btn");
    const listEl = section.querySelector(".comment-list");

    function submitComment() {
        const text = textarea.value.trim();
        if (text === "") return;

        submitBtn.disabled = true;

        postForm("actions/comment.php", { post_id: postId, comment_text: text })
            .then(function (res) {
                submitBtn.disabled = false;
                const data = res.data;

                if (data.error) {
                    showToast(data.error);
                    return;
                }

                textarea.value = "";

                // Update this post's comment count badge (both the icon's
                // count and, if this section is reused elsewhere, keep in sync)
                const commentBtn = document.querySelector('.comment-btn[data-post-id="' + postId + '"]');
                if (commentBtn) {
                    const countEl = commentBtn.querySelector(".comment-count");
                    if (countEl) countEl.textContent = data.count;
                }

                loadComments(postId, listEl);
            })
            .catch(function () {
                submitBtn.disabled = false;
                showToast("Could not post comment right now.");
            });
    }

    submitBtn.addEventListener("click", submitComment);

    textarea.addEventListener("keydown", function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === "Enter") {
            submitComment();
        }
    });
});

// ==========================================================
// Share Menu — only records a share when a real destination is chosen
// ==========================================================
const shareMenu = document.getElementById("shareMenu");
let activeSharePostId = null;
let activeShareTitle = "";
let activeShareBtn = null;

function buildShareLink(postId) {
    return window.location.origin + window.location.pathname + "?post=" + encodeURIComponent(postId);
}

function recordShare(postId, btn) {
    postForm("actions/share.php", { post_id: postId })
        .then(function (res) {
            const data = res.data;
            if (data.error) return;

            if (btn) {
                const countEl = btn.querySelector(".share-count");
                if (countEl) countEl.textContent = data.count;

                if (data.already_shared) {
                    btn.classList.add("already-shared");
                    btn.title = "You already shared this post";
                }
            }
        })
        .catch(function () {});
}

function openShareMenu(btn) {
    activeSharePostId = btn.getAttribute("data-post-id");
    activeShareTitle = btn.getAttribute("data-title") || "this post";
    activeShareBtn = btn;

    const rect = btn.getBoundingClientRect();
    shareMenu.style.top = (window.scrollY + rect.bottom + 8) + "px";
    shareMenu.style.left = (window.scrollX + rect.left) + "px";
    shareMenu.classList.add("active");
}

function closeShareMenu() {
    shareMenu.classList.remove("active");
    activeSharePostId = null;
    activeShareBtn = null;
}

document.querySelectorAll(".share-btn:not(.share-profile-btn)").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
        e.stopPropagation();

        if (btn.classList.contains("disabled-action")) {
            showToast("You can't share your own post.");
            return;
        }

        openShareMenu(btn);
    });
});

document.addEventListener("click", function (e) {
    if (shareMenu && shareMenu.classList.contains("active") && !shareMenu.contains(e.target)) {
        closeShareMenu();
    }
});

if (shareMenu) {
    shareMenu.querySelectorAll(".share-option").forEach(function (opt) {
        opt.addEventListener("click", function () {
            if (!activeSharePostId) return;

            const link = buildShareLink(activeSharePostId);
            const text = "Check out \"" + activeShareTitle + "\" on UIU Talent Hunt!";
            const action = opt.getAttribute("data-action");

            switch (action) {
                case "copy":
                    navigator.clipboard.writeText(link).then(function () {
                        showToast("Link copied to clipboard!");
                    }).catch(function () {
                        showToast("Could not copy link.");
                    });
                    break;

                case "whatsapp":
                    window.open("https://wa.me/?text=" + encodeURIComponent(text + " " + link), "_blank");
                    break;

                case "facebook":
                    window.open("https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(link), "_blank");
                    break;

                case "twitter":
                    window.open("https://twitter.com/intent/tweet?text=" + encodeURIComponent(text) + "&url=" + encodeURIComponent(link), "_blank");
                    break;

                case "email":
                    window.location.href = "mailto:?subject=" + encodeURIComponent(activeShareTitle) + "&body=" + encodeURIComponent(text + " " + link);
                    break;
            }

            recordShare(activeSharePostId, activeShareBtn);
            closeShareMenu();
        });
    });
}

// ==========================================================
// Blog "See more / See less" — only shown when content overflows
// ==========================================================
document.querySelectorAll(".blog-content-preview").forEach(function (el) {
    el.classList.add("clamped");
    const toggleBtn = el.parentElement.querySelector('.blog-see-more-btn[data-target="' + el.id + '"]');
    if (!toggleBtn) return;

    requestAnimationFrame(function () {
        if (el.scrollHeight > el.clientHeight + 2) {
            toggleBtn.style.display = "inline-block";
        }
    });

    toggleBtn.addEventListener("click", function () {
        const expanded = el.classList.toggle("expanded");
        el.classList.toggle("clamped", !expanded);
        toggleBtn.textContent = expanded ? "See less" : "See more";
    });
});

// ==========================================================
// Report Menu — records a report only when a reason is chosen
// ==========================================================
const reportMenu = document.getElementById("reportMenu");
let activeReportPostId = null;
let activeReportBtn = null;

function openReportMenu(btn) {
    activeReportPostId = btn.getAttribute("data-post-id");
    activeReportBtn = btn;

    const rect = btn.getBoundingClientRect();
    reportMenu.style.top = (window.scrollY + rect.bottom + 8) + "px";
    reportMenu.style.left = (window.scrollX + rect.left) + "px";
    reportMenu.classList.add("active");
}

function closeReportMenu() {
    reportMenu.classList.remove("active");
    activeReportPostId = null;
    activeReportBtn = null;
}

document.querySelectorAll(".report-btn").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
        e.stopPropagation();

        if (btn.classList.contains("disabled-action")) {
            showToast("You can't report your own post.");
            return;
        }
        if (btn.classList.contains("already-reported")) {
            showToast("You already reported this post.");
            return;
        }

        openReportMenu(btn);
    });
});

document.addEventListener("click", function (e) {
    if (reportMenu && reportMenu.classList.contains("active") && !reportMenu.contains(e.target)) {
        closeReportMenu();
    }
});

if (reportMenu) {
    reportMenu.querySelectorAll(".report-option").forEach(function (opt) {
        opt.addEventListener("click", function () {
            if (!activeReportPostId) return;

            const reason = opt.getAttribute("data-reason");
            const btn = activeReportBtn;

            postForm("actions/report_post.php", { post_id: activeReportPostId, reason: reason })
                .then(function (res) {
                    const data = res.data;

                    if (data.error) {
                        showToast(data.error);
                        return;
                    }

                    if (btn) {
                        btn.classList.add("already-reported");
                        btn.title = "You already reported this post";
                    }

                    showToast(data.already_reported ? "You already reported this post." : "Post reported. Our team will review it.");
                })
                .catch(function () {
                    showToast("Could not submit report right now.");
                });

            closeReportMenu();
        });
    });
}
