<div>
    <h1 class="text-2xl font-bold uk-margin-bottom ah-admin-title">Posts Management</h1>

    {{-- Flash Messages --}}
    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }}" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    {{-- Filters --}}
    <div class="uk-margin uk-grid-small uk-child-width-1-3@s uk-flex-middle" uk-grid>
        <div>
            <input 
                wire:model.live.debounce.500ms="search" 
                class="uk-input" 
                type="text" 
                placeholder="Search posts by title..." 
                aria-label="Search posts"
            >
        </div>
        <div>
            <select wire:model="statusFilter" class="uk-select" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
            </select>
        </div>
        <div>
            <select wire:model="categoryFilter" class="uk-select" aria-label="Filter by category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="uk-width-1-1 uk-margin-small-top">
            <label>
                <input 
                    wire:model="showTrashed" 
                    type="checkbox" 
                    class="uk-checkbox" 
                    aria-label="Show trashed posts"
                >
                Show Trashed Posts
            </label>
        </div>
    </div>

    {{-- Posts Table --}}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-middle uk-table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Login Name By</th>
                    <th>Post Publish By</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="uk-text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ optional($post->createdBy)->name ?? 'N/A' }}</td>
                        <td>{{ $post->users->pluck('name')->join(', ') ?: 'N/A' }}</td>
                        <td>{{ optional($post->category)->name ?? 'N/A' }}</td>
                        <td>
                            <span 
                                class="uk-label 
                                    {{ $post->status === 'published' ? 'uk-label-success' 
                                        : ($post->status === 'draft' ? 'uk-label-warning' 
                                        : 'uk-label-primary') }}"
                            >
                                {{ ucfirst($post->status) }}
                                @if ($post->trashed())
                                    (Trashed)
                                @endif
                            </span>
                        </td>
                        
                        <td class="uk-text-center">

    <div class="uk-flex uk-flex-center uk-flex-wrap">
        @if (!$post->trashed())
            <a href="{{ route('admin.posts.edit', $post->id) }}" 
               class="uk-button uk-button-text uk-margin-small-right" 
               uk-tooltip="Edit Post">
               Edit
            </a>
            <button 
                wire:click="confirmDelete({{ $post->id }})" 
                class="uk-button uk-button-text uk-text-danger" 
                uk-tooltip="Move to Trash">
                Delete
            </button>
        @else
            <button 
                wire:click="restorePost({{ $post->id }})" 
                class="uk-button uk-button-text" 
                uk-tooltip="Restore Post">
                Restore
            </button>
        @endif
    </div>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="uk-text-center">No posts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="uk-margin">
        {{ $posts->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
  <div id="delete-modal" uk-modal>
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        Livewire.on('showDeleteModal', () => {
            UIkit.modal('#delete-modal').show();
        });
        Livewire.on('hideDeleteModal', () => {
            UIkit.modal('#delete-modal').hide();
        });
    });
</script>
</div>
