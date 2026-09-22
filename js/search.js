// ==========================================================
// Live student/talent search (navbar search box)
// ==========================================================
(function () {
    const searchBox = document.getElementById("searchBox");
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");

    if (!searchBox || !searchInput || !searchResults) return;

    let debounceTimer = null;

    function escapeHtml(str) {
        const div = document.createElement("div");
        div.textContent = str;
        return div.innerHTML;
    }

    function renderResults(results) {
        if (!results || results.length === 0) {
            searchResults.innerHTML = '<p class="search-no-results">No students found.</p>';
            searchResults.classList.add("active");
            return;
        }

        searchResults.innerHTML = results.map(function (r) {
            return (
                '<a class="search-result-item" href="public_profile.php?user_id=' + encodeURIComponent(r.user_id) + '">' +
                    '<img src="' + r.profile_image + '" alt="">' +
                    '<div class="search-result-info">' +
                        '<h4>' + escapeHtml(r.full_name) + '</h4>' +
                        '<p>' + escapeHtml(r.student_id) + ' • ' + escapeHtml(r.department_code) + '</p>' +
                    '</div>' +
                '</a>'
            );
        }).join("");

        searchResults.classList.add("active");
    }

    searchInput.addEventListener("input", function () {
        const q = searchInput.value.trim();

        clearTimeout(debounceTimer);

        if (q === "") {
            searchResults.classList.remove("active");
            searchResults.innerHTML = "";
            return;
        }

        debounceTimer = setTimeout(function () {
            fetch("actions/search_users.php?q=" + encodeURIComponent(q))
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.error) return;
                    renderResults(data.results);
                })
                .catch(function () {
                    searchResults.innerHTML = '<p class="search-no-results">Search failed. Try again.</p>';
                    searchResults.classList.add("active");
                });
        }, 300);
    });

    // Close the dropdown when clicking outside the search box
    document.addEventListener("click", function (e) {
        if (!searchBox.contains(e.target)) {
            searchResults.classList.remove("active");
        }
    });

    // Re-open results (if any) when refocusing the input with text still in it
    searchInput.addEventListener("focus", function () {
        if (searchInput.value.trim() !== "" && searchResults.innerHTML !== "") {
            searchResults.classList.add("active");
        }
    });
})();
