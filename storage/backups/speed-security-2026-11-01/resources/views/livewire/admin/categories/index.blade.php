<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Categories</h1>
            <p class="ah-page-sub">{{ $categories->total() }} {{ Str::plural('category', $categories->total()) }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.categories.create') }}" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add Category</span>
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
                    placeholder="Search categories..."
                    aria-label="Search categories"
                >
            </div>
        </div>

        {{-- Categories Table --}}
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
                        <th class="ah-col-name">Parent</th>
                        <th class="ah-col-num">Posts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="ah-col-main">
                                <div class="ah-tree" style="--ah-tree-depth: {{ $category->depth ?? 0 }}">
                                    @if (($category->depth ?? 0) > 0)
                                        <span class="ah-tree-mark" aria-hidden="true">&#8627;</span>
                                    @endif
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="ah-table-title">{{ $category->name }}</a>
                                </div>
                            </td>
                            <td class="ah-muted ah-nowrap">{{ $category->slug }}</td>
                            <td class="ah-col-name">
                                @if ($category->parent)
                                    {{ $category->parent->name }}
                                @else
                                    <span class="ah-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="ah-col-num">
                                @if ($category->posts_count > 0)
                                    <a href="{{ route('admin.posts.index', ['categoryFilter' => $category->id]) }}" aria-label="View {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }} in {{ $category->name }}">{{ number_format($category->posts_count) }}</a>
                                @else
                                    0
                                @endif
                            </td>
                            <td>
                                <div class="ah-row-actions">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}">Edit</a>
                                    <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $category->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ah-empty">No categories found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($categories->hasPages())
            <div class="ah-card-foot">
                {{ $categories->links('partials.admin-pagination') }}
            </div>
        @endif
    </div>

    @include('partials.admin-delete-modal', ['title' => 'Delete Category', 'action' => 'delete'])
</div>
