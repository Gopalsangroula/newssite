{{-- Media picker for the post and page editors: window.openImagePicker({ multiple, onSelect }) --}}
<div wire:ignore>
    <div id="imagePickerModal" class="uk-modal-container" uk-modal="container: false; bg-close: false">
        <div class="uk-modal-dialog ah-picker">
            <div class="ah-picker-head">
                <h2 class="ah-card-title">Media Library</h2>
                <button class="uk-modal-close" type="button" uk-close aria-label="Close"></button>
            </div>

            <div class="ah-picker-toolbar">
                <input id="mediaSearchInput" class="uk-input" type="search" placeholder="Search media..." aria-label="Search media">
                <label class="uk-button uk-button-default" for="quickUploadInput">
                    <span uk-icon="icon: upload; ratio: 0.9"></span>
                    <span>Upload files</span>
                </label>
                <input id="quickUploadInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden>
            </div>

            <div class="ah-picker-body">
                <div id="mediaPickerGrid" class="ah-picker-grid"></div>
                <div class="ah-picker-more">
                    <button id="pickerLoadMore" class="uk-button uk-button-default" type="button" hidden>Load more</button>
                </div>
            </div>

            <div class="ah-picker-foot">
                <span id="pickerStatus" class="ah-picker-status"></span>
                <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
                <button id="pickerInsert" class="uk-button uk-button-primary" type="button" disabled>Select</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const listUrl = @json(route('admin.media.list'));
            const uploadUrl = @json(route('admin.media.upload'));
            const pageSize = 40;

            const modal = document.getElementById('imagePickerModal');
            const grid = document.getElementById('mediaPickerGrid');
            const search = document.getElementById('mediaSearchInput');
            const fileInput = document.getElementById('quickUploadInput');
            const loadMore = document.getElementById('pickerLoadMore');
            const insert = document.getElementById('pickerInsert');
            const status = document.getElementById('pickerStatus');

            const fileTypes = {
                image: /\.(jpe?g|png|gif|webp|avif|svg)$/i,
                video: /\.(mp4|webm|ogv|mov|m4v)$/i,
                audio: /\.(mp3|wav|ogg|m4a)$/i,
            };

            let images = [];
            let visible = 0;
            let selected = new Map();
            let picker = { multiple: false, allowedTypes: ['image'], onSelect: () => {} };
            let searchTimer;

            const cleanUrl = (url) => url.replace(/(\/storage){2,}/, '/storage');
            const fileType = (url) => Object.keys(fileTypes).find((type) => fileTypes[type].test(url.split('?')[0]));

            const updateFooter = () => {
                insert.disabled = selected.size === 0;
                status.textContent = selected.size ? `${selected.size} selected` : `${images.length} items`;
            };

            const toggleSelection = (image, tile) => {
                if (selected.has(image.url)) {
                    selected.delete(image.url);
                    tile.classList.remove('is-selected');
                } else {
                    if (!picker.multiple) {
                        selected.clear();
                        grid.querySelectorAll('.is-selected').forEach((item) => item.classList.remove('is-selected'));
                    }
                    selected.set(image.url, image);
                    tile.classList.add('is-selected');
                }
                updateFooter();
            };

            const finish = () => {
                const chosen = Array.from(selected.values());
                UIkit.modal(modal).hide();
                if (chosen.length) {
                    picker.onSelect(chosen);
                }
            };

            const createTile = (image) => {
                const tile = document.createElement('button');
                tile.type = 'button';
                tile.className = 'ah-picker-item';
                tile.title = image.alt;
                tile.classList.toggle('is-selected', selected.has(image.url));

                if (image.type === 'image') {
                    const preview = document.createElement('img');
                    preview.src = image.url;
                    preview.alt = image.alt;
                    preview.loading = 'lazy';
                    tile.append(preview);
                } else {
                    tile.textContent = image.alt;
                }

                tile.addEventListener('click', () => toggleSelection(image, tile));
                tile.addEventListener('dblclick', () => {
                    selected.set(image.url, image);
                    finish();
                });
                return tile;
            };

            const showMore = () => {
                images.slice(visible, visible + pageSize).forEach((image) => grid.append(createTile(image)));
                visible = Math.min(visible + pageSize, images.length);
                loadMore.hidden = visible >= images.length;
            };

            const showMessage = (text) => {
                const message = document.createElement('p');
                message.className = 'ah-empty';
                message.textContent = text;
                grid.replaceChildren(message);
                loadMore.hidden = true;
            };

            const loadImages = async (term = '') => {
                showMessage('Loading...');
                try {
                    const response = await fetch(`${listUrl}?search=${encodeURIComponent(term)}`, {
                        headers: { Accept: 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error(`Server returned ${response.status}`);
                    }

                    const items = await response.json();
                    images = items
                        .map((item) => ({
                            url: cleanUrl(item.url),
                            alt: item.alt || item.original_name || '',
                            type: fileType(item.url),
                        }))
                        .filter((item) => picker.allowedTypes.includes(item.type));
                    visible = 0;
                    grid.replaceChildren();

                    if (images.length) {
                        showMore();
                    } else {
                        showMessage(term ? 'Nothing matches your search.' : 'No media found here yet.');
                    }
                } catch (error) {
                    showMessage(`Could not load images: ${error.message}`);
                }
                updateFooter();
            };

            window.uploadMediaFile = async (file) => {
                const body = new FormData();
                body.append('media_file', file);

                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body,
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok || !result.media) {
                    throw new Error(result.error || result.message || 'Upload failed');
                }
                return { url: cleanUrl(result.media.url), alt: '', type: 'image' };
            };

            const uploadFiles = async (files) => {
                if (!files.length) {
                    return;
                }
                status.textContent = 'Uploading...';
                try {
                    const uploaded = await Promise.all(Array.from(files).map(window.uploadMediaFile));
                    if (!picker.multiple) {
                        selected.clear();
                    }
                    uploaded.slice(picker.multiple ? 0 : -1).forEach((image) => selected.set(image.url, image));
                    await loadImages(search.value);
                } catch (error) {
                    UIkit.notification(error.message, { status: 'danger' });
                    updateFooter();
                }
            };

            window.openImagePicker = ({ multiple = false, allowedTypes = ['image'], onSelect }) => {
                picker = { multiple, allowedTypes, onSelect };
                selected = new Map();
                search.value = '';
                insert.textContent = multiple ? 'Add selected' : 'Select';
                UIkit.modal(modal).show();
                loadImages();
            };

            search.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => loadImages(search.value), 300);
            });
            fileInput.addEventListener('change', async () => {
                await uploadFiles(fileInput.files);
                fileInput.value = '';
            });
            modal.addEventListener('dragover', (event) => event.preventDefault());
            modal.addEventListener('drop', (event) => {
                event.preventDefault();
                uploadFiles(event.dataTransfer.files);
            });
            loadMore.addEventListener('click', showMore);
            insert.addEventListener('click', finish);
        })();
    </script>
</div>
