/*
 * Reader tools on the public pages: dark mode, saved posts, text size and the search bar stay in
 * the reader's own browser. The stored pages are the same for everyone, so anything that talks
 * to the server happens here too: comments, and the count of reads.
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

    function showCommentErrors(form, errors) {
        form.querySelectorAll('[data-error]').forEach(function (line) {
            var messages = errors[line.dataset.error];

            line.textContent = messages ? messages[0] : '';
            line.hidden = !messages;
        });
    }

    function sendComment(form) {
        var button = form.querySelector('[type="submit"]');
        var failed = { form: ['प्रतिक्रिया पठाउन सकिएन। केही बेरपछि फेरि प्रयास गर्नुहोस्।'] };

        button.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' },
        }).then(function (response) {
            return response.json().then(function (answer) {
                if (!response.ok) {
                    showCommentErrors(form, answer.errors || failed);
                    return;
                }

                showCommentErrors(form, {});
                form.elements.body.value = '';
                form.hidden = true;
                document.querySelector('[data-comment-sent]').hidden = false;
            });
        }).catch(function () {
            showCommentErrors(form, failed);
        }).then(function () {
            button.disabled = false;
        });
    }

    function countRead() {
        var story = document.querySelector('[data-read-id]');

        // A page opened in the background by the browser, or by a crawler, is not a read.
        if (story && navigator.sendBeacon && !document.prerendering
            && !/bot|crawl|spider|slurp|headless|preview|lighthouse/i.test(navigator.userAgent)) {
            navigator.sendBeacon('/read/' + story.dataset.readId);
        }
    }

    function playVideo(link) {
        var player = document.createElement('iframe');
        var box = link.parentNode;

        // Facebook draws its player to the size it is given, so it gets the size of the box.
        player.src = link.dataset.videoId
            ? 'https://www.youtube-nocookie.com/embed/' + link.dataset.videoId + '?autoplay=1&rel=0'
            : 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=true'
                + '&width=' + Math.round(box.clientWidth) + '&height=' + Math.round(box.clientHeight)
                + '&href=' + encodeURIComponent(link.dataset.facebookVideo);
        player.title = link.getAttribute('aria-label');
        player.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        player.allowFullscreen = true;
        link.replaceWith(player);
    }

    var breakingJump = 0;

    function markBreaking(link) {
        document.querySelectorAll('[data-breaking-story]').forEach(function (other) {
            if (other === link) {
                other.setAttribute('aria-current', 'true');
            } else {
                other.removeAttribute('aria-current');
            }
        });
    }

    // A time in the breaking news timeline scrolls the stories beside it. On a phone those
    // stories do not scroll, so the link opens the story instead.
    function showBreaking(link) {
        var feed = document.querySelector('[data-breaking-feed]');
        var story = document.getElementById(link.dataset.breakingStory);

        if (!feed || !story || story.offsetParent !== feed || getComputedStyle(feed).overflowY !== 'auto') {
            return false;
        }

        // The scroll passes other stories on its way; they must not take the highlight from this one.
        clearTimeout(breakingJump);
        breakingJump = setTimeout(function () {
            breakingJump = 0;
        }, 900);

        markBreaking(link);
        feed.scrollTop = story.offsetTop - parseFloat(getComputedStyle(feed).paddingTop);

        return true;
    }

    // As the reader scrolls the stories, the time of the one at the top lights up.
    function watchBreaking() {
        var feed = document.querySelector('[data-breaking-feed]');

        if (!feed || !window.IntersectionObserver) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var link = entry.isIntersecting && !breakingJump
                    && document.querySelector('[data-breaking-story="' + entry.target.id + '"]');

                if (link) {
                    markBreaking(link);
                }
            });
        }, { root: feed, rootMargin: '0px 0px -75% 0px' });

        feed.querySelectorAll('article[id]').forEach(function (story) {
            observer.observe(story);
        });
    }

    document.addEventListener('submit', function (event) {
        if (event.target.matches('[data-comment-form]')) {
            event.preventDefault();
            sendComment(event.target);
        }
    });

    document.addEventListener('click', function (event) {
        var target = event.target;
        var control;

        if ((control = target.closest('[data-video-id], [data-facebook-video]'))) {
            event.preventDefault();
            playVideo(control);
        } else if ((control = target.closest('[data-breaking-story]'))) {
            if (!event.ctrlKey && !event.metaKey && !event.shiftKey && showBreaking(control)) {
                event.preventDefault();
            }
        } else if (target.closest('[data-comment-again]')) {
            document.querySelector('[data-comment-sent]').hidden = true;
            document.querySelector('[data-comment-form]').hidden = false;
        } else if ((control = target.closest('[data-theme-toggle]'))) {
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
    countRead();
    watchBreaking();

    var size = parseInt(read(SIZE_KEY), 10);

    if (!isNaN(size) && SIZES[size]) {
        applySize(size);
    }
})();
