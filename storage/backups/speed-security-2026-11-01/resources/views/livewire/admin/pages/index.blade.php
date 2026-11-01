<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Pages Management</h1>
            <p class="ah-page-sub">{{ $pages->total() }} {{ Str::plural('page', $pages->total()) }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.pages.create') }}" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add New Page</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    <div class="ah-card">
        {{-- Filters --}}
        <div class="ah-toolbar">
            <div class="ah-toolbar-grow">
                <input
                    wire:model.live.debounce.500ms="search"
                    class="uk-input"
                    type="text"
                    placeholder="Search pages by title..."
                    aria-label="Search pages"
                >
            </div>
            <select wire:model.live="status" class="uk-select" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
            </select>
            <label>
                <input wire:model.live="showTrashed" type="checkbox" class="uk-checkbox">
                Show Trashed
            </label>
        </div>

        {{-- Pages Table --}}
        <div
            class="ah-table-wrap"
            wire:loading.class="ah-is-loading"
            wire:target="search,status,showTrashed,gotoPage,previousPage,nextPage"
        >
            <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                <thead>
                    <tr>
                        <th class="ah-col-thumb">Cover</th>
                        <th class="ah-col-main">Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        @php
                            $statusBadge = match (true) {
                                $page->trashed() => 'ah-badge--danger',
                                $page->status === 'published' => 'ah-badge--success',
                                $page->status === 'draft' => 'ah-badge--warning',
                                $page->status === 'scheduled' => 'ah-badge--info',
                                default => '',
                            };
                            $pageDate = $page->published_at ?? $page->created_at;
                            $thumbTag = $page->trashed() ? 'span' : 'a';
                        @endphp
                        <tr wire:key="page-{{ $page->id }}">
                            <td class="ah-col-thumb">
                                <{{ $thumbTag }}
                                    class="ah-thumb-link"
                                    @unless ($page->trashed())
                                        href="{{ route('admin.pages.edit', $page->id) }}"
                                        aria-label="Edit {{ $page->title }}"
                                    @endunless
                                >
                                    @if ($page->cover_image)
                                        <img
                                            src="{{ $page->cover_image_url }}"
                                            alt=""
                                            class="ah-thumb"
                                            width="72"
                                            height="48"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        <span class="ah-thumb ah-thumb--empty">
                                            <span uk-icon="icon: image" wire:ignore></span>
                                        </span>
                                    @endif
                                </{{ $thumbTag }}>
                            </td>
                            <td class="ah-col-main">
                                @if ($page->trashed())
                                    <span class="ah-table-title ah-clamp-2">{{ $page->title }}</span>
                                @else
                                    <a href="{{ route('admin.pages.edit', $page->id) }}" class="ah-table-title ah-clamp-2">{{ $page->title }}</a>
                                @endif
                                <span class="ah-table-meta">Created by {{ $page->creator->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="ah-badge {{ $statusBadge }}">{{ $page->trashed() ? 'Trashed' : ucfirst($page->status) }}</span>
                            </td>
                            <td class="ah-col-date">{{ $pageDate?->format('M j, Y') ?? 'N/A' }}</td>
                            <td>
                                <div class="ah-row-actions">
                                    @if ($page->trashed())
                                        <button type="button" wire:click="restore({{ $page->id }})">Restore</button>
                                    @else
                                        <a href="{{ route('admin.pages.edit', $page->id) }}">Edit</a>
                                        @if ($page->isPublished() && $page->slug)
                                            <a href="{{ route('frontend.frontend.pages.show', $page->slug) }}" target="_blank" rel="noopener">View on site</a>
                                        @endif
                                        <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $page->id }})">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ah-empty">No pages found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($pages->hasPages())
            <div class="ah-card-foot">
                {{ $pages->links('partials.admin-pagination') }}
            </div>
        @endif
    </div>

    {{--
        Delete Confirmation Modal.
        "container: false" stops UIkit moving it to <body>: outside the Livewire root
        its wire:click buttons throw. wire:ignore stops a re-render wiping UIkit's state.
    --}}
    <div id="delete-modal" class="ah-modal" uk-modal="container: false" wire:ignore>
        <div class="uk-modal-dialog">
            <button class="uk-modal-close-default" type="button" uk-close aria-label="Close modal"></button>
            <div class="uk-modal-header">
                <h2 class="uk-modal-title">Confirm Deletion</h2>
            </div>
            <div class="uk-modal-body">
                <p>Are you sure you want to delete this page?</p>
            </div>
            <div class="uk-modal-footer uk-text-right">
                <button
                    class="uk-button uk-button-default"
                    type="button"
                    wire:click="$dispatch('hideDeleteModal')"
                >
                    Cancel
                </button>
                <button
                    class="uk-button uk-button-danger"
                    type="button"
                    wire:click="delete"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('showDeleteModal', () => UIkit.modal('#delete-modal').show());
        $wire.on('hideDeleteModal', () => UIkit.modal('#delete-modal').hide());
    </script>
    @endscript
</div>
