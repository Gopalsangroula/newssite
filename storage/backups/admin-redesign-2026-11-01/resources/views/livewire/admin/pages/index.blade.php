<div>

    <h1 class="text-2xl font-bold uk-margin-bottom ah-admin-title">Pages Management</h1>

    {{-- Flash Messages --}}
    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }}" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    {{-- Filters --}}
    <div class="uk-margin uk-grid-small uk-child-width-1-3@s uk-flex-middle" uk-grid>

        {{-- Search --}}
        <div>
            <input
                wire:model.live.debounce.500ms="search"
                class="uk-input"
                type="text"
                placeholder="Search pages by title..."
            >
        </div>

        {{-- Status Filter --}}
        <div>
            <select wire:model.live="status" class="uk-select">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
            </select>
        </div>

        {{-- Trashed Toggle --}}
        <div class="uk-flex uk-flex-middle">
            <label class="uk-flex uk-flex-middle">
                <input
                    wire:model.live="showTrashed"
                    type="checkbox"
                    class="uk-checkbox"
                >
                <span class="uk-margin-small-left">Show Trashed Pages</span>
            </label>
        </div>

    </div>

    {{-- Pages Table --}}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-middle uk-table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th class="uk-text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pages as $page)
                    <tr wire:key="page-{{ $page->id }}">

                        <td>{{ $page->title }}</td>

                        <td>{{ $page->creator->name ?? 'N/A' }}</td>

                        <td>
                            <span class="uk-label
                                @if ($page->status === 'published')
                                    uk-label-success
                                @elseif ($page->status === 'draft')
                                    uk-label-warning
                                @else
                                    uk-label-primary
                                @endif
                            ">
                                {{ ucfirst($page->status) }}
                                @if ($page->trashed())
                                    (Trashed)
                                @endif
                            </span>
                        </td>

                        <td class="uk-text-center">
                            <div class="uk-flex uk-flex-center uk-flex-wrap">

                                @if (! $page->trashed())
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.pages.edit', $page->id) }}"
                                       class="uk-button uk-button-text uk-margin-small-right">
                                        Edit
                                    </a>

                                    {{-- Move to Trash --}}
                                    <button
                                        wire:click="confirmDelete({{ $page->id }})"
                                        class="uk-button uk-button-text uk-text-danger">
                                        Delete
                                    </button>

                                @else
                                    {{-- Restore --}}
                                    <button
                                        wire:click="restore({{ $page->id }})"
                                        class="uk-button uk-button-text">
                                        Restore
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="uk-text-center">No pages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="uk-margin">
        {{ $pages->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="delete-modal" uk-modal>
        <div class="uk-modal-dialog">

            <button class="uk-modal-close-default" uk-close></button>

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

</div>

{{-- Modal Script --}}
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
