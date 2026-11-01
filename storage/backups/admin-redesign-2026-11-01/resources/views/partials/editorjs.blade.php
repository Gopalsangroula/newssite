<script>
    // Make sure @this is available and working
    document.addEventListener('livewire:init', () => {
        setTimeout(() => {
            if (typeof tinymce !== 'undefined') {
                initTinyMCE();
            } else {
                console.error('TinyMCE is not loaded');
            }
        }, 100);
    });

    document.addEventListener('livewire:navigated', () => {
        if (tinymce.get('editor')) {
            tinymce.get('editor').remove();
        }
        setTimeout(() => {
            if (typeof tinymce !== 'undefined') {
                initTinyMCE();
            }
        }, 100);
    });

    // This function calls your Livewire component's setCoverImage method
    window.openImagePickerForCover = function () {
        console.log('Opening image picker for cover');
        window.openImagePicker(null, (url) => {
            console.log('Selected cover image URL:', url);
            
            // Clean the URL before sending
            let cleanUrl = url;
            cleanUrl = cleanUrl.replace('/storage/storage/', '/storage/');
            cleanUrl = cleanUrl.replace('storage/storage/', 'storage/');
            cleanUrl = cleanUrl.replace(/(\/storage){2,}/, '/storage');
            
            console.log('Cleaned URL:', cleanUrl);
            
            if (cleanUrl && typeof @this !== 'undefined') {
                @this.call('setCoverImage', cleanUrl);
            }
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
            toolbar_mode: 'sliding',
            toolbar_sticky: true,
            image_dimensions: true,
            image_advtab: true,
            object_resizing: 'img',
            resize_img_proportional: true,
            automatic_uploads: true,
            paste_data_images: true,
            content_style: `
              body{
                    font-family:Arial,sans-serif;
                    font-size:16px;
                }
                .mce-content-body img[data-mce-selected],
                .mce-content-body video[data-mce-selected],
                .mce-content-body table[data-mce-selected]{
                    outline:3px solid #b4d7ff;
                    outline-offset:2px;
                }
                img{
                    height:auto;
                    display:inline-block;
                    max-width:100%;
                }
                figure.image{
                    display:table;
                    margin:1rem auto;
                }
                figure.image img{
                    max-width:none;
                }
            `,
            toolbar: [
                'undo redo | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image media storageimage',
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
                editor.ui.registry.addButton('storageimage', {
                    text: '📂 Browse Library',
                    onAction: () => window.openImagePicker(editor),
                    classes: 'tox-tbtn--primary'
                });

                editor.on('change input', () => {
                    const content = editor.getContent();
                    if (typeof @this !== 'undefined') {
                        @this.set('content', content);
                    }
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

        // Setup quick upload
        const quickUploadBtn = document.getElementById('quickUploadBtn');
        const quickUploadInput = document.getElementById('quickUploadInput');
        const quickUploadProgress = document.getElementById('quickUploadProgress');
        
        if (quickUploadBtn && quickUploadInput) {
            const newBtn = quickUploadBtn.cloneNode(true);
            quickUploadBtn.parentNode.replaceChild(newBtn, quickUploadBtn);
            
            newBtn.onclick = async () => {
                const file = quickUploadInput.files[0];
                if (!file) {
                    if (typeof UIkit !== 'undefined') {
                        UIkit.notification('Please select an image first', { status: 'warning' });
                    }
                    return;
                }
                
                const formData = new FormData();
                formData.append('media_file', file);
                
                if (quickUploadProgress) quickUploadProgress.style.display = 'block';
                
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const response = await fetch('{{ route('admin.media.upload') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    
                    if (!response.ok) throw new Error('Upload failed');
                    
                    const result = await response.json();
                    if (typeof UIkit !== 'undefined') {
                        UIkit.notification('Image uploaded successfully!', { status: 'success' });
                    }
                    
                    quickUploadInput.value = '';
                    if (quickUploadProgress) quickUploadProgress.style.display = 'none';
                    fetchAndDisplayImages(currentSearchTerm);
                    
                } catch (error) {
                    console.error('Upload error:', error);
                    if (typeof UIkit !== 'undefined') {
                        UIkit.notification('Upload failed: ' + error.message, { status: 'danger' });
                    }
                    if (quickUploadProgress) quickUploadProgress.style.display = 'none';
                }
            };
        }

        if (!modal || !grid || !searchInput || !pagination) {
            console.error('Required DOM elements not found');
            if (typeof UIkit !== 'undefined') {
                UIkit.notification('Error: Image picker initialization failed', { status: 'danger' });
            }
            return;
        }

        if (typeof UIkit !== 'undefined') {
            UIkit.modal(modal).show();
        }
        
        grid.innerHTML = '<p class="uk-text-center"><span uk-spinner></span> Loading images...</p>';

        const renderImages = (images, page) => {
            grid.innerHTML = '';
            const start = (page - 1) * imagesPerPage;
            const end = start + imagesPerPage;
            const paginatedImages = images.slice(start, end);

            if (!paginatedImages.length) {
                grid.innerHTML = '<p class="uk-text-center">No images found. Upload some images to get started.</p>';
                pagination.innerHTML = '';
                return;
            }

            paginatedImages.forEach(image => {
                const div = document.createElement('div');
                div.className = 'uk-card uk-card-default uk-card-hover uk-card-body uk-text-center';
                div.style.padding = '10px';
                
                const img = document.createElement('img');
                img.src = image.url;
                img.style.width = '100%';
                img.style.height = '150px';
                img.style.objectFit = 'cover';
                img.style.cursor = 'pointer';
                img.style.borderRadius = '4px';
                img.alt = image.alt || image.original_name || 'Media Image';
                img.loading = 'lazy';

                img.onerror = () => {
                    img.src = 'https://via.placeholder.com/300x150?text=Image+Not+Found';
                    img.style.objectFit = 'contain';
                    img.style.padding = '20px';
                };

                img.onclick = () => {
                    // Clean the URL before using
                    let cleanUrl = image.url;
                    cleanUrl = cleanUrl.replace('/storage/storage/', '/storage/');
                    cleanUrl = cleanUrl.replace(/(\/storage){2,}/, '/storage');
                    
                    if (editor) {
                        const tempImg = new Image();
                        tempImg.onload = () => {
                            let width = tempImg.width;
                            let height = tempImg.height;
                            if (width > 800) {
                                const ratio = 800 / width;
                                width = 800;
                                height = Math.round(height * ratio);
                            }
                            editor.insertContent(`
                                <img
                                    src="${cleanUrl}"
                                    alt="${image.alt || image.original_name || 'Media Image'}"
                                    width="${width}"
                                    height="${height}"
                                    style="height:auto; max-width:100%;"
                                />
                            `);
                            if (typeof UIkit !== 'undefined') {
                                UIkit.notification('Image inserted successfully', { status: 'success' });
                            }
                        };
                        tempImg.src = cleanUrl;
                    } else if (callback) {
                        callback(cleanUrl);
                        if (typeof UIkit !== 'undefined') {
                            UIkit.notification('Image selected', { status: 'success' });
                        }
                    }
                    if (typeof UIkit !== 'undefined') {
                        UIkit.modal(modal).hide();
                    }
                };
                
                const label = document.createElement('p');
                label.className = 'uk-text-small uk-text-muted uk-margin-small-top uk-text-truncate';
                label.textContent = image.original_name || image.alt || 'Untitled';
                label.style.maxWidth = '100%';
                
                div.appendChild(img);
                div.appendChild(label);
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
                let startPage = Math.max(1, page - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'uk-active' : '';
                    li.innerHTML = `<a href="#" onclick="window.changePage(${i});return false;">${i}</a>`;
                    pagination.appendChild(li);
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
                     image.original_name?.toLowerCase().includes(currentSearchTerm.toLowerCase()) ||
                     image.url.toLowerCase().includes(currentSearchTerm.toLowerCase())))
                : allImages;
            renderImages(filteredImages, currentPage);
        };

        const fetchAndDisplayImages = async (searchTerm = '') => {
            currentSearchTerm = searchTerm;
            grid.innerHTML = '<p class="uk-text-center"><span uk-spinner></span> Loading images...</p>';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) throw new Error('CSRF token not found');

                const response = await fetch(`{{ route('admin.media.list') }}?t=${new Date().getTime()}&search=${encodeURIComponent(searchTerm)}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });

                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                
                const images = await response.json();
                if (!Array.isArray(images)) throw new Error('Invalid response format: Expected an array');

                allImages = images;
                currentPage = 1;
                renderImages(allImages, currentPage);
            } catch (err) {
                console.error('Error fetching images:', err);
                grid.innerHTML = `<p class="uk-text-center uk-text-danger">Error loading images: ${err.message}</p>`;
                pagination.innerHTML = '';
                if (typeof UIkit !== 'undefined') {
                    UIkit.notification(`Error loading images: ${err.message}`, { status: 'danger' });
                }
            }
        };

        window.refreshImageGrid = () => fetchAndDisplayImages(currentSearchTerm);

        const newSearchInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newSearchInput, searchInput);
        
        newSearchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchAndDisplayImages(e.target.value), 300);
        });

        fetchAndDisplayImages();
    };

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initTinyMCE, 100);
    });
</script>

<style>
    .post-content img,
    .entry-content img,
    .article-content img {
        max-width: 100%;
        height: auto;
    }
    .tox .tox-edit-area__iframe {
        max-width: 100% !important;
    }
</style>