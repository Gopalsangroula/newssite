{{-- Gutenberg block editor for the post and page forms. Expects #editor and #title inside .ah-block-editor. --}}
@php
    $blockEditorCdn = 'https://cdn.jsdelivr.net/npm/@automattic/isolated-block-editor@2.31.0/build-browser';
@endphp

@assets
    <link rel="stylesheet" href="{{ $blockEditorCdn }}/core.css">
    <link rel="stylesheet" href="{{ $blockEditorCdn }}/isolated-block-editor.css">
    <script src="https://cdn.jsdelivr.net/npm/react@18.3.1/umd/react.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@18.3.1/umd/react-dom.production.min.js"></script>
    <script src="{{ $blockEditorCdn }}/isolated-block-editor.js"></script>
@endassets

@script
<script>
    const textarea = document.getElementById('editor');
    const title = document.getElementById('title');
    const shell = textarea.closest('.ah-block-editor');
    const fields = textarea.closest('.ah-editor-fields');

    const mediaUpload = ({ filesList, onFileChange, onError }) => {
        const files = Array.from(filesList);
        if (files.some((file) => !file.type.startsWith('image/'))) {
            onError('Only images can be uploaded here. For video or audio, use Insert from URL.');
            return;
        }

        Promise.all(files.map(window.uploadMediaFile))
            .then(onFileChange)
            .catch((error) => onError(error.message));
    };

    // Gutenberg's Media Library buttons open WordPress's wp.media modal, which only
    // exists inside WordPress. These classes stand in for it with the site's picker.
    class MediaFrame {
        static extend() {
            return class extends this {};
        }

        constructor(options) {
            this.options = options;
            this.listeners = {};
            this.chosen = [];
            this.$el = { addClass: () => {} };
            this.content = { get: () => null, mode: () => {} };
        }

        on(event, listener) {
            this.listeners[event] = listener;
        }

        selection() {
            return {
                models: this.chosen.map((image) => ({ toJSON: () => image })),
                toJSON: () => this.chosen,
            };
        }

        state() {
            return { get: () => this.selection() };
        }

        finish() {
            this.listeners.select?.();
        }

        open() {
            window.openImagePicker({
                multiple: Boolean(this.options.multiple),
                allowedTypes: this.options.mimeType,
                onSelect: (images) => {
                    this.chosen = images;
                    this.finish();
                },
            });
        }

        remove() {}

        detach() {}
    }

    class GalleryFrame extends MediaFrame {
        finish() {
            this.listeners.update?.(this.selection());
        }
    }

    const placeTitle = () => {
        const content = shell.querySelector('.interface-interface-skeleton__content');
        if (content && title.parentElement !== content) {
            content.prepend(title);
        }
    };

    title.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            shell.querySelector('[contenteditable="true"], .block-editor-default-block-appender__content')?.focus();
        }
    });

    // Gutenberg writes to the textarea without firing an input event, so send its
    // value along with whichever action button was clicked.
    fields.addEventListener('click', (event) => {
        if (event.target.closest('[wire\\:click]')) {
            $wire.$set('content', textarea.value, false);
        }
    }, true);

    // Searchable pickers: the category takes one value, tags and authors take several.
    fields.querySelectorAll('select[data-choice]').forEach((select) => {
        // Choices overwrites data-choice with its own "active" marker, so the field name is read first.
        const field = select.dataset.choice;

        new Choices(select, {
            removeItemButton: select.multiple,
            searchEnabled: true,
            searchPlaceholderValue: 'Type to search',
            // Every match, not the first four, and close matches only, so "शाखा" lists all the branches.
            searchResultLimit: 100,
            fuseOptions: { threshold: 0.3 },
            shouldSort: false,
            itemSelectText: '',
        });

        select.addEventListener('change', () => {
            const values = Array.from(select.selectedOptions).map((option) => option.value);
            $wire.$set(field, select.multiple ? values : (values[0] ?? ''), false);
        });
    });

    window.openImagePickerForCover = () => {
        window.openImagePicker({ onSelect: ([image]) => $wire.setCoverImage(image.url) });
    };

    new MutationObserver(placeTitle).observe(shell, { childList: true, subtree: true });

    // Plain HTML comments would make Gutenberg treat old posts as block markup.
    if (!/<!--\s*wp:/.test(textarea.value)) {
        textarea.value = textarea.value.replace(/<!--[\s\S]*?-->/g, '');
    }

    window.wp.attachEditor(textarea, {
        iso: {
            preferencesKey: 'ah-block-editor',
            defaultPreferences: { fixedToolbar: false },
            moreMenu: { editor: true, fullscreen: false, preview: false, topToolbar: false },
            toolbar: { inserter: true, inspector: true, navigation: true, undo: true },
            sidebar: { inspector: true, inserter: false },
            blocks: {
                allowBlocks: [
                    'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/quote', 'core/pullquote',
                    'core/image', 'core/gallery', 'core/cover', 'core/media-text', 'core/video', 'core/audio',
                    'core/table', 'core/columns', 'core/column', 'core/group', 'core/buttons', 'core/button',
                    'core/separator', 'core/spacer', 'core/details', 'core/verse', 'core/preformatted', 'core/code',
                    'core/html',
                ],
            },
        },
        editor: {
            hasFixedToolbar: false,
            hasUploadPermissions: true,
            mediaUpload,
            bodyPlaceholder: 'Type / to choose a block',
        },
    });

    window.wp.media = {
        view: { MediaFrame: { Select: MediaFrame, Post: GalleryFrame }, settings: { post: {} } },
        model: { Selection: class {} },
        query: () => ({ models: [], props: { toJSON: () => ({}) } }),
    };
</script>
@endscript
