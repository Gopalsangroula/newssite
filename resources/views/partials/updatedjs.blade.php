
<script>
document.addEventListener('livewire:init', () => {
    setTimeout(() => {
        if (typeof tinymce !== 'undefined') {
            initTinyMCE();
            if (@this.content) {
                tinymce.get('editor')?.setContent(@json($content));
            }
        } else {
            console.error('TinyMCE is not loaded');
        }
    }, 100);
});

document.addEventListener('livewire:navigated', () => {
    tinymce.get('editor')?.remove();
    setTimeout(() => {
        if (typeof tinymce !== 'undefined') {
            initTinyMCE();
            if (@this.content) {
                tinymce.get('editor')?.setContent(@json($content));
            }
        }
    }, 100);
});

window.openImagePickerForCover = function () {
    console.log('Opening image picker for cover');
    window.openImagePicker(null, (url) => {
        console.log('Selected cover image URL:', url);
        @this.set('cover_image_url', url);
    });
};

function initTinyMCE() {
    if (tinymce.get('editor')) {
        tinymce.get('editor').remove();
    }

    tinymce.init({
        selector: '#editor',
        height: 500,
        menubar: 'file edit view insert format tools table help',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table emoticons hr pagebreak wordcount help codesample',
        toolbar_mode: 'sliding', // keeps top bar compact
        toolbar_sticky: true,    // ✅ Main focus bar stays pinned at the top
        toolbar: [
            'undo redo | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image media storageimage',

            // Extended Toolbar
            'styleselect formatselect fontselect fontsizeselect | outdent indent | blockquote hr pagebreak | table emoticons charmap codesample | code fullscreen preview print | ltr rtl'
        ].join(' | '),

        images_upload_handler: async (blobInfo, success, failure) => {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) throw new Error('CSRF token not found');

                const formData = new FormData();
                formData.append('editor_image', blobInfo.blob(), blobInfo.filename());

                const response = await fetch('{{ route('admin.posts.editor-upload') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

                const result = await response.json();
                if (result?.location) {
                    success(result.location);
                    if (typeof window.refreshImageGrid === 'function') {
                        setTimeout(() => window.refreshImageGrid(), 500);
                    }
                } else {
                    failure('Upload failed: Invalid server response');
                }
            } catch (err) {
                failure(`Upload failed: ${err.message}`);
            }
        },

        setup: (editor) => {
            // ✅ Highlighted custom Browse Library button
            editor.ui.registry.addButton('storageimage', {
                text: '📂 Browse Library',
                onAction: () => window.openImagePicker(editor),
                classes: 'tox-tbtn--primary' // 🔥 makes it blue/highlighted
            });

            editor.on('change input', () => {
                @this.set('content', editor.getContent());
            });
        },

        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
        image_advtab: true,
        paste_data_images: true,
        browser_spellcheck: true,
        branding: false,
        promotion: false
    });
}

window.openImagePicker = function (editor, callback) {
    const modal = document.getElementById('imagePickerModal');
    const grid = document.getElementById('imageGrid');
    const searchInput = document.getElementById('mediaSearchInput');
    const pagination = document.getElementById('pagination');
    const imagesPerPage = 12;
    let currentPage = 1;
    let allImages = [];
    let currentSearchTerm = '';
    let searchTimeout;

    if (!modal || !grid || !searchInput || !pagination) {
        console.error('Required DOM elements not found');
        UIkit.notification('Error: Image picker initialization failed', { status: 'danger' });
        return;
    }

    UIkit.modal(modal).show();
    grid.innerHTML = '<p class="uk-text-center">Loading images...</p>';

    const renderImages = (images, page) => {
        grid.innerHTML = '';
        const start = (page - 1) * imagesPerPage;
        const end = start + imagesPerPage;
        const paginatedImages = images.slice(start, end);

        if (!paginatedImages.length) {
            grid.innerHTML = '<p class="uk-text-center">No images found.</p>';
            pagination.innerHTML = '';
            return;
        }

        paginatedImages.forEach(image => {
            const div = document.createElement('div');
            div.className = 'uk-card uk-card-default uk-card-hover';
            const img = document.createElement('img');
            img.src = image.url;
            img.style.width = '100%';
            img.style.cursor = 'pointer';
            img.alt = image.alt || 'Media Image';
            img.loading = 'lazy';

            img.onerror = () => {
                img.src = '';
                img.alt = 'Failed to load image';
                img.style.border = '1px solid red';
            };

            img.onclick = () => {
                if (editor) {
                    editor.insertContent(`<img src="${image.url}" alt="${image.alt || 'Media Image'}" />`);
                } else if (callback) {
                    callback(image.url);
                }
                UIkit.modal(modal).hide();
            };

            div.appendChild(img);
            grid.appendChild(div);
        });

        const totalPages = Math.ceil(images.length / imagesPerPage);
        pagination.innerHTML = '';
        if (totalPages > 1) {
            const prevLi = document.createElement('li');
            prevLi.className = page === 1 ? 'uk-disabled' : '';
            prevLi.innerHTML = `<a href="#" onclick="window.changePage(${page - 1});return false;"><span uk-pagination-previous></span></a>`;
            pagination.appendChild(prevLi);

            const maxVisiblePages = 5;
            const half = Math.floor(maxVisiblePages / 2);
            let startPage = Math.max(1, page - half);
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                const firstLi = document.createElement('li');
                firstLi.innerHTML = `<a href="#" onclick="window.changePage(1);return false;">1</a>`;
                pagination.appendChild(firstLi);
                if (startPage > 2) {
                    const dotsLi = document.createElement('li');
                    dotsLi.innerHTML = '<span>...</span>';
                    pagination.appendChild(dotsLi);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = i === page ? 'uk-active' : '';
                li.innerHTML = `<a href="#" onclick="window.changePage(${i});return false;">${i}</a>`;
                pagination.appendChild(li);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dotsLi = document.createElement('li');
                    dotsLi.innerHTML = '<span>...</span>';
                    pagination.appendChild(dotsLi);
                }
                const lastLi = document.createElement('li');
                lastLi.innerHTML = `<a href="#" onclick="window.changePage(${totalPages});return false;">${totalPages}</a>`;
                pagination.appendChild(lastLi);
            }

            const nextLi = document.createElement('li');
            nextLi.className = page === totalPages ? 'uk-disabled' : '';
            nextLi.innerHTML = `<a href="#" onclick="window.changePage(${page + 1});return false;"><span uk-pagination-next></span></a>`;
            pagination.appendChild(nextLi);
        }
    };

    window.changePage = (page) => {
        if (page < 1 || page > Math.ceil(allImages.length / imagesPerPage)) return;
        currentPage = page;
        const filteredImages = currentSearchTerm
            ? allImages.filter(image =>
                (image.alt?.toLowerCase().includes(currentSearchTerm.toLowerCase()) ||
                 image.url.toLowerCase().includes(currentSearchTerm.toLowerCase())))
            : allImages;
        renderImages(filteredImages, currentPage);
    };

    const fetchAndDisplayImages = async (searchTerm = '') => {
        currentSearchTerm = searchTerm;
        grid.innerHTML = '<p class="uk-text-center">Loading images...</p>';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) throw new Error('CSRF token not found');

            const response = await fetch(`{{ route('admin.media.list') }}?t=${new Date().getTime()}&search=${encodeURIComponent(searchTerm)}`, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            
            const images = await response.json();
            if (!Array.isArray(images)) throw new Error('Invalid response format: Expected an array');

            console.log('Media library images:', images);
            allImages = images;
            currentPage = 1;
            renderImages(allImages, currentPage);
        } catch (err) {
            console.error('Error fetching images:', err);
            grid.innerHTML = `<p class="uk-text-center uk-text-danger">Error loading images: ${err.message}</p>`;
            pagination.innerHTML = '';
            UIkit.notification(`Error loading images: ${err.message}`, { status: 'danger' });
        }
    };

    window.refreshImageGrid = () => fetchAndDisplayImages(currentSearchTerm);

    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchAndDisplayImages(e.target.value), 300);
    });

    Livewire.on('mediaUploaded', () => {
        setTimeout(() => fetchAndDisplayImages(currentSearchTerm), 500);
    });

    fetchAndDisplayImages();
};

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initTinyMCE, 100);
});

Livewire.on('resetEditor', initTinyMCE);
</script>