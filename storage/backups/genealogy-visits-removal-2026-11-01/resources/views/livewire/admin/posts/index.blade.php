<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Posts Management</h1>
            <p class="ah-page-sub">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.posts.create') }}" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add New Post</span>
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
                    placeholder="Search posts by title..."
                    aria-label="Search posts"
                >
            </div>
            <select wire:model.live="statusFilter" class="uk-select" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
            </select>
            <select wire:model.live="categoryFilter" class="uk-select" aria-label="Filter by category">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <label>
                <input wire:model.live="showTrashed" type="checkbox" class="uk-checkbox">
                Show Trashed
            </label>
        </div>

        {{-- Posts Table --}}
        <div
            class="ah-table-wrap"
            wire:loading.class="ah-is-loading"
            wire:target="search,statusFilter,categoryFilter,showTrashed,gotoPage,previousPage,nextPage"
        >
            <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                <thead>
                    <tr>
                        <th class="ah-col-thumb">Cover</th>
                        <th class="ah-col-main">Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="ah-col-num">Views</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        @php
                            $statusBadge = match (true) {
                                $post->trashed() => 'ah-badge--danger',
                                $post->status === 'published' => 'ah-badge--success',
                                $post->status === 'draft' => 'ah-badge--warning',
                                $post->status === 'scheduled' => 'ah-badge--info',
                                default => '',
                            };
                            $publishedBy = $post->users->pluck('name')->join(', ');
                            $postDate = $post->published_at ?? $post->created_at;
                            $thumbTag = $post->trashed() ? 'span' : 'a';
                        @endphp
                        <tr wire:key="post-{{ $post->id }}">
                            <td class="ah-col-thumb">
                                <{{ $thumbTag }}
                                    class="ah-thumb-link"
                                    @unless ($post->trashed())
                                        href="{{ route('admin.posts.edit', $post->id) }}"
                                        aria-label="Edit {{ $post->title }}"
                                    @endunless
                                >
                                    @if ($post->cover_image)
                                        <img
                                            src="{{ Storage::url($post->cover_image) }}"
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
                                @if ($post->trashed())
                                    <span class="ah-table-title ah-clamp-2">{{ $post->title }}</span>
                                @else
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="ah-table-title ah-clamp-2">{{ $post->title }}</a>
                                @endif
                                <span class="ah-table-meta">
                                    Added by {{ optional($post->createdBy)->name ?? 'N/A' }}
                                    @if ($publishedBy !== '')
                                        &middot; Published by {{ $publishedBy }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ optional($post->category)->name ?? 'N/A' }}</td>
                            <td>
                                <span class="ah-badge {{ $statusBadge }}">{{ $post->trashed() ? 'Trashed' : ucfirst($post->status) }}</span>
                            </td>
                            <td class="ah-col-date">{{ $postDate?->format('M j, Y') ?? 'N/A' }}</td>
                            <td class="ah-col-num">{{ number_format($post->visits_count) }}</td>
                            <td>
                                <div class="ah-row-actions">
                                    @if ($post->trashed())
                                        <button type="button" wire:click="restorePost({{ $post->id }})">Restore</button>
                                    @else
                                        <a href="{{ route('admin.posts.edit', $post->id) }}">Edit</a>
                                        @if ($post->isPublished() && $post->slug)
                                            <a href="{{ route('frontend.details.show', $post->slug) }}" target="_blank" rel="noopener">View on site</a>
                                        @endif
                                        <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $post->id }})">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="ah-empty">No posts found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($posts->hasPages())
            <div class="ah-card-foot">
                {{ $posts->links('partials.admin-pagination') }}
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
            <button
                class="uk-modal-close-default"
                type="button"
                uk-close
                wire:click="$dispatch('hideDeleteModal')"
                aria-label="Close modal"
            ></button>
            <div class="uk-modal-header">
                <h2 class="uk-modal-title">Confirm Deletion</h2>
            </div>
            <div class="uk-modal-body">
                <p>Are you sure you want to delete this post? This will move it to the trash.</p>
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
                    wire:click="deletePost"
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
