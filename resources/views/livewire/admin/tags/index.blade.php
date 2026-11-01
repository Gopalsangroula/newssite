<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Tags</h1>
            <p class="ah-page-sub">{{ $tags->total() }} {{ Str::plural('tag', $tags->total()) }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.tags.create') }}" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add Tag</span>
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
        @if ($tags->total() === 0 && trim($search) === '')
            {{-- Empty State --}}
            <div class="ah-empty ah-empty--section">
                <span class="ah-empty-icon" uk-icon="icon: tag; ratio: 2" wire:ignore></span>
                <strong class="ah-empty-title">No tags yet</strong>
                <p>Tags group related posts across categories. Add your first tag to get started.</p>
                <a href="{{ route('admin.tags.create') }}" class="uk-button uk-button-primary">Add Tag</a>
            </div>
        @else
            {{-- Filters --}}
            <div class="ah-toolbar">
                <div class="ah-toolbar-grow">
                    <input
                        wire:model.live.debounce.500ms="search"
                        class="uk-input"
                        type="text"
                        placeholder="Search tags..."
                        aria-label="Search tags"
                    >
                </div>
            </div>

            {{-- Tags Table --}}
            <div
                class="ah-table-wrap"
                wire:loading.class="ah-is-loading"
                wire:target="search,gotoPage,previousPage,nextPage"
            >
                <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                    <thead>
                        <tr>
                            <th class="ah-col-main">Name</th>
                            <th>Slug</th>
                            <th class="ah-col-num">Posts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tags as $tag)
                            <tr wire:key="tag-{{ $tag->id }}">
                                <td class="ah-col-main">
                                    <a href="{{ route('admin.tags.edit', $tag->id) }}" class="ah-table-title">{{ $tag->name }}</a>
                                </td>
                                <td class="ah-muted ah-nowrap">{{ $tag->slug }}</td>
                                <td class="ah-col-num">{{ number_format($tag->posts_count) }}</td>
                                <td>
                                    <div class="ah-row-actions">
                                        <a href="{{ route('admin.tags.edit', $tag->id) }}">Edit</a>
                                        <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $tag->id }})">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="ah-empty">No tags match your search.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($tags->hasPages())
                <div class="ah-card-foot">
                    {{ $tags->links('partials.admin-pagination') }}
                </div>
            @endif
        @endif
    </div>

    @include('partials.admin-delete-modal', ['title' => 'Delete Tag', 'action' => 'delete'])
</div>
