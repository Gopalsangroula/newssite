/*
 * Reader tools on the public pages: dark mode, saved posts, text size and the search bar.
 * Everything stays in the reader's own browser.
 */
(function () {
    'use strict';

    var THEME_KEY = 'libang-theme';
    var SAVED_KEY = 'libang-saved-posts';
    var SIZE_KEY = 'libang-post-size';
    var SIZES = [0.9, 1, 1.15, 1.3];

    function read(key) {
        try {
            return localStorage.getItem(key);
        } catch (error) {
            return null;
        }
    }

    function write(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (error) {
            // Private windows can refuse storage. The tool then lasts for this page only.
        }
    }

    function savedPosts() {
        try {
            return JSON.parse(read(SAVED_KEY)) || [];
        } catch (error) {
            return [];
        }
    }

    function storeSaved(posts) {
        write(SAVED_KEY, JSON.stringify(posts));
    }

    function refreshSaveButtons() {
        var saved = savedPosts();

        document.querySelectorAll('[data-save-post]').forEach(function (button) {
            var isSaved = saved.some(function (post) {
                return post.url === button.dataset.url;
            });

            button.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
            button.querySelector('[data-save-label]').textContent = isSaved ? 'सेभ गरियो' : 'सेभ गर्नुहोस्';
        });
    }

    function renderSavedList() {
        var list = document.querySelector('[data-saved-list]');

        if (!list) {
            return;
        }

        var saved = savedPosts();
        var empty = document.querySelector('[data-saved-empty]');

        list.textContent = '';
        empty.hidden = saved.length > 0;

        saved.forEach(function (post) {
            var item = document.createElement('li');
            var body = document.createElement('div');
            var link = document.createElement('a');
            var remove = document.createElement('button');

            if (post.image) {
                var image = document.createElement('img');
                image.src = post.image;
                image.alt = '';
                image.loading = 'lazy';
                item.appendChild(image);
            }

            link.href = post.url;
            link.textContent = post.title;
            remove.type = 'button';
            remove.textContent = 'हटाउनुहोस्';
            remove.dataset.removeSaved = post.url;

            body.appendChild(link);
            body.appendChild(remove);
            item.appendChild(body);

            if (!post.image) {
                body.style.gridColumn = '1 / -1';
            }

            list.appendChild(item);
        });
    }

    function toggleSaved(button) {
        var saved = savedPosts();
        var url = button.dataset.url;
        var remaining = saved.filter(function (post) {
            return post.url !== url;
        });

        if (remaining.length === saved.length) {
            remaining.unshift({ url: url, title: button.dataset.title, image: button.dataset.image });
        }

        storeSaved(remaining);
        refreshSaveButtons();
    }

    function applySize(step) {
        var entry = document.querySelector('.ah-post-entry');

        if (entry) {
            entry.style.setProperty('--post-text-scale', SIZES[step]);
        }
    }

    function changeSize(change) {
        var current = parseInt(read(SIZE_KEY), 10);
        var step = Math.min(SIZES.length - 1, Math.max(0, (isNaN(current) ? 1 : current) + change));

        write(SIZE_KEY, String(step));
        applySize(step);
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        var control;

        if ((control = target.closest('[data-theme-toggle]'))) {
            var isDark = document.documentElement.dataset.theme === 'dark';

            if (isDark) {
                delete document.documentElement.dataset.theme;
            } else {
                document.documentElement.dataset.theme = 'dark';
            }

            write(THEME_KEY, isDark ? 'light' : 'dark');
        } else if ((control = target.closest('[data-save-post]'))) {
            toggleSaved(control);
        } else if ((control = target.closest('[data-remove-saved]'))) {
            storeSaved(savedPosts().filter(function (post) {
                return post.url !== control.dataset.removeSaved;
            }));
            renderSavedList();
        } else if ((control = target.closest('[data-post-size]'))) {
            changeSize(parseInt(control.dataset.postSize, 10));
        } else if (target.closest('[data-search-toggle]')) {
            // UIkit shows the bar on this same click, so wait for it before moving the cursor there.
            setTimeout(function () {
                var input = document.getElementById('site-search-input');

                if (input && input.offsetParent !== null) {
                    input.focus();
                }
            }, 80);
        }
    });

    refreshSaveButtons();
    renderSavedList();

    var size = parseInt(read(SIZE_KEY), 10);

    if (!isNaN(size) && SIZES[size]) {
        applySize(size);
    }
})();
