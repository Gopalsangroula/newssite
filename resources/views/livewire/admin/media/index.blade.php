<div class="ahm"
     x-data="ahmLibrary()"
     x-on:keydown.window="onKey($event)"
     x-on:media-filtered.window="clearSelection()"
     x-on:media-page-changed.window="$refs.library.getBoundingClientRect().top < 0 && $refs.library.scrollIntoView({ behavior: 'smooth' })"
     x-on:media-notice.window="notify($event.detail)">

    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Media Library</h1>
            <p class="ah-page-sub">Browse, search and manage every file on the site.</p>
        </div>
        <div class="ah-page-actions">
            <button type="button" class="uk-button uk-button-primary" aria-controls="ahm-upload-panel"
                    :aria-expanded="uploadOpen" x-on:click="uploadOpen = ! uploadOpen">
                @include('livewire.admin.media.partials.icon', ['name' => 'upload'])
                <span>Add New</span>
            </button>
        </div>
    </div>

    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $flash => $flashClass)
        @if (session($flash))
            <div class="{{ $flashClass }}" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($flash) }}</p>
            </div>
        @endif
    @endforeach

    <section class="ah-card ahm-section" id="ahm-upload-panel" x-show="uploadOpen" x-cloak>
        <div class="ah-card-head">
            <h2 class="ah-card-title">Upload files</h2>
            <button type="button" class="ahm-icon-btn" title="Close" aria-label="Close the upload area" x-on:click="uploadOpen = false">
                @include('livewire.admin.media.partials.icon', ['name' => 'close'])
            </button>
        </div>
        <div class="ah-card-body">
            <livewire:admin.media.uploader wire:key="media-uploader" />
        </div>
    </section>

    <section class="ah-card ahm-section" x-ref="library">
        <div class="ah-toolbar ahm-toolbar">
            <div class="ahm-views" role="group" aria-label="View">
                <button type="button" class="ahm-view {{ $mode === 'list' ? '' : 'is-active' }}" title="Grid view" aria-label="Grid view"
                        aria-pressed="{{ $mode === 'list' ? 'false' : 'true' }}" wire:click="$set('mode', 'grid')">
                    @include('livewire.admin.media.partials.icon', ['name' => 'grid'])
                </button>
                <button type="button" class="ahm-view {{ $mode === 'list' ? 'is-active' : '' }}" title="List view" aria-label="List view"
                        aria-pressed="{{ $mode === 'list' ? 'true' : 'false' }}" wire:click="$set('mode', 'list')">
                    @include('livewire.admin.media.partials.icon', ['name' => 'list'])
                </button>
            </div>

            @if ($canDelete)
                <button type="button" class="uk-button uk-button-default ahm-bulk-toggle" :aria-pressed="selecting" x-on:click="toggleSelectMode()">
                    Bulk select
                </button>
            @endif

            <select wire:model.live="type" class="uk-select" aria-label="Filter by type">
                <option value="">All media items</option>
                @foreach (\App\Models\Media::KINDS as $kind => $kindLabel)
                    <option value="{{ $kind }}">{{ $kindLabel }}</option>
                @endforeach
            </select>

            <select wire:model.live="month" class="uk-select" aria-label="Filter by month uploaded">
                <option value="">All dates</option>
                @foreach ($months as $monthValue => $monthLabel)
                    <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                @endforeach
            </select>

            <select wire:model.live="sort" class="uk-select" aria-label="Sort">
                @foreach ($sorts as $sortKey => $sortOption)
                    <option value="{{ $sortKey }}">{{ $sortOption[2] }}</option>
                @endforeach
            </select>

            <div class="ah-toolbar-grow ahm-search">
                @include('livewire.admin.media.partials.icon', ['name' => 'search'])
                <input wire:model.live.debounce.400ms="search" class="uk-input" type="search"
                       placeholder="Search by name" aria-label="Search media">
                <span class="ahm-count" aria-live="polite">
                    {{ number_format($mediaItems->total()) }} {{ Str::plural('item', $mediaItems->total()) }}
                </span>
            </div>
        </div>

        @if ($canDelete)
            <div class="ahm-bulk" x-show="selecting" x-cloak>
                <span class="ahm-bulk-count" x-text="count + ' selected'" aria-live="polite"></span>
                <button type="button" class="uk-button uk-button-default uk-button-small" x-on:click="selectPage()">Select all on this page</button>
                <button type="button" class="uk-button uk-button-default uk-button-small" x-on:click="clearSelection()">Cancel</button>
                <button type="button" class="uk-button uk-button-danger uk-button-small ahm-bulk-delete" :disabled="count === 0" x-on:click="askBulkDelete()">
                    Delete permanently
                </button>
            </div>
        @endif

        <div class="ahm-results {{ $mode === 'list' ? 'ahm-results--list' : '' }}" :class="{ 'ahm-selecting': selecting }"
             wire:loading.class="is-loading" wire:target="search, type, month, sort, perPage, mode, sortBy, gotoPage, nextPage, previousPage, clearFilters, bulkDelete">
            @if ($mediaItems->isEmpty())
                <div class="ah-empty">
                    @if ($filtered)
                        <p>No media matches these filters.</p>
                        <button type="button" class="uk-button uk-button-default uk-button-small" wire:click="clearFilters">Clear filters</button>
                    @else
                        <p>No media yet. Use Add New to upload your first file.</p>
                    @endif
                </div>
            @elseif ($mode === 'list')
                @include('livewire.admin.media.partials.list')
            @else
                @include('livewire.admin.media.partials.grid')
            @endif
        </div>

        @if ($mediaItems->total() > $perPageOptions[0])
            <div class="ah-card-foot ahm-foot">
                <select wire:model.live="perPage" class="uk-select ahm-perpage" aria-label="Items per page">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }} per page</option>
                    @endforeach
                </select>
                {{ $mediaItems->links('partials.admin-pagination') }}
            </div>
        @endif
    </section>

    {{-- Shown the moment an item is clicked, until its details arrive from the server. --}}
    <div class="ahm-modal" x-show="opening" x-cloak>
        <div class="ahm-modal-backdrop" x-on:click="opening = false"></div>
        <span class="ahm-spinner" aria-hidden="true"></span>
    </div>

    @if ($detail)
        @include('livewire.admin.media.partials.details')
    @endif

    <div class="ahm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="ahm-dialog-title" aria-describedby="ahm-dialog-text"
         x-show="dialog.open" x-cloak>
        <div class="ahm-dialog-box" x-trap="dialog.open">
            <h2 id="ahm-dialog-title" class="ah-card-title" x-text="dialog.title"></h2>
            <p id="ahm-dialog-text" x-text="dialog.text"></p>
            <ul class="ahm-dialog-names" x-show="dialog.names.length">
                <template x-for="(name, index) in dialog.names" :key="index">
                    <li x-text="name"></li>
                </template>
            </ul>
            <p class="ahm-dialog-warning" x-show="dialog.warning" x-text="dialog.warning"></p>
            <div class="ahm-dialog-actions">
                <button type="button" class="uk-button uk-button-default" autofocus :disabled="dialog.busy" x-on:click="closeDialog()">Cancel</button>
                <button type="button" class="uk-button uk-button-danger" :disabled="dialog.busy" x-on:click="confirmDialog()" x-text="dialog.confirmLabel"></button>
            </div>
        </div>
    </div>

    <script>
        window.ahmLibrary = function () {
            return {
                uploadOpen: false,
                selectMode: false,
                selected: {},
                opening: false,
                dirty: false,
                dialog: { open: false, busy: false, title: '', text: '', names: [], warning: '', confirmLabel: '', action: null },

                get count() {
                    return Object.keys(this.selected).length;
                },

                get selecting() {
                    return this.selectMode || this.count > 0;
                },

                isSelected(id) {
                    return id in this.selected;
                },

                allSelected(ids) {
                    return ids.length > 0 && ids.every((id) => id in this.selected);
                },

                pageItems() {
                    return [...this.$root.querySelectorAll('.ahm-item')];
                },

                add(item) {
                    const id = Number(item.dataset.mediaId);
                    this.selected[id] = { id, name: item.dataset.mediaName, attached: item.dataset.mediaAttached === '1' };
                },

                toggle(item) {
                    const id = Number(item.dataset.mediaId);

                    if (id in this.selected) {
                        delete this.selected[id];
                    } else {
                        this.add(item);
                    }
                },

                selectPage() {
                    this.pageItems().forEach((item) => this.add(item));
                },

                deselectPage() {
                    this.pageItems().forEach((item) => delete this.selected[Number(item.dataset.mediaId)]);
                },

                clearSelection() {
                    this.selected = {};
                    this.selectMode = false;
                },

                toggleSelectMode() {
                    if (this.selecting) {
                        this.clearSelection();
                    } else {
                        this.selectMode = true;
                    }
                },

                tileClick(button, id) {
                    if (this.selecting) {
                        this.toggle(button.closest('.ahm-item'));
                    } else {
                        this.show(id);
                    }
                },

                show(id) {
                    this.opening = true;
                    this.$wire.openItem(id).finally(() => {
                        this.opening = false;
                    });
                },

                step(id) {
                    this.guard(() => this.$wire.openItem(id));
                },

                closeDetails() {
                    this.guard(() => this.$wire.closeItem());
                },

                // Runs action, first asking when the description has unsaved changes.
                guard(action) {
                    if (! this.dirty) {
                        return action();
                    }

                    this.ask({
                        title: 'Discard your changes?',
                        text: 'The description has not been saved.',
                        confirmLabel: 'Discard changes',
                        action: async () => {
                            this.dirty = false;
                            await action();
                        },
                    });
                },

                ask(options) {
                    this.dialog = { open: true, busy: false, names: [], warning: '', ...options };
                },

                closeDialog() {
                    if (! this.dialog.busy) {
                        this.dialog.open = false;
                    }
                },

                async confirmDialog() {
                    this.dialog.busy = true;

                    try {
                        await this.dialog.action();
                    } finally {
                        this.dialog.busy = false;
                        this.dialog.open = false;
                    }
                },

                askDelete(item) {
                    const id = Number(item.dataset.mediaId);

                    this.ask({
                        title: 'Delete this file permanently?',
                        text: 'The file will be removed from the site. This cannot be undone.',
                        names: [item.dataset.mediaName],
                        warning: item.dataset.mediaAttached === '1' ? 'It is attached to a post or page and will disappear from there.' : '',
                        confirmLabel: 'Delete permanently',
                        action: async () => {
                            await this.$wire.deleteMedia(id);
                            delete this.selected[id];
                            this.dirty = false;
                        },
                    });
                },

                askBulkDelete() {
                    const items = Object.values(this.selected);
                    const attached = items.filter((item) => item.attached).length;
                    const names = items.slice(0, 5).map((item) => item.name);

                    if (items.length > names.length) {
                        names.push('and ' + (items.length - names.length) + ' more');
                    }

                    this.ask({
                        title: items.length === 1 ? 'Delete 1 item permanently?' : 'Delete ' + items.length + ' items permanently?',
                        text: 'The files will be removed from the site. This cannot be undone.',
                        names,
                        warning: attached > 0 ? attached + ' of these attached to a post or page will disappear from there.' : '',
                        confirmLabel: 'Delete permanently',
                        action: async () => {
                            await this.$wire.bulkDelete(items.map((item) => item.id));
                            this.clearSelection();
                        },
                    });
                },

                notify({ message, status }) {
                    if (window.UIkit) {
                        UIkit.notification({ message, status, pos: 'top-right' });
                    }
                },

                onKey(event) {
                    if (this.dialog.open) {
                        if (event.key === 'Escape') {
                            this.closeDialog();
                        }
                        return;
                    }

                    if (this.opening) {
                        if (event.key === 'Escape') {
                            this.opening = false;
                        }
                        return;
                    }

                    if (! this.$wire.item) {
                        return;
                    }

                    if (event.key === 'Escape') {
                        this.closeDetails();
                    } else if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
                        if (event.key === 'ArrowLeft') {
                            this.$refs.previous?.click();
                        } else if (event.key === 'ArrowRight') {
                            this.$refs.next?.click();
                        }
                    }
                },
            };
        };

        window.ahmDetail = function () {
            return {
                description: '',
                saved: '',
                dimensions: 'Loading…',
                broken: false,
                copied: false,
                saving: false,

                init() {
                    this.description = this.saved = this.$el.dataset.description || '';

                    // A cached image can finish before Alpine binds its load listener.
                    this.$nextTick(() => {
                        const image = this.$refs.image;

                        if (image && image.complete) {
                            image.naturalWidth ? this.measure(image.naturalWidth, image.naturalHeight) : this.markBroken();
                        }
                    });
                },

                measure(width, height) {
                    this.dimensions = width + ' by ' + height + ' pixels';
                },

                markBroken() {
                    this.broken = true;
                    this.dimensions = 'Unknown';
                },

                async save(id) {
                    this.saving = true;

                    try {
                        if (await this.$wire.saveItem(id, this.description)) {
                            this.saved = this.description;
                        }
                    } finally {
                        this.saving = false;
                    }
                },

                copy() {
                    const input = this.$refs.url;
                    const done = () => {
                        this.copied = true;
                        setTimeout(() => {
                            this.copied = false;
                        }, 1800);
                    };
                    const fallback = () => {
                        input.select();

                        if (document.execCommand('copy')) {
                            done();
                        }
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(input.value).then(done, fallback);
                    } else {
                        fallback();
                    }
                },
            };
        };
    </script>
</div>
