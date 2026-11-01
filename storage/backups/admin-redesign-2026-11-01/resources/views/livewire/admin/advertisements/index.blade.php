<div>
    <h2 class="uk-heading-line"><span>Advertisements</span></h2>

    <!-- Success Notification -->
    <div wire:ignore id="success-notification" style="display: none;" class="uk-alert-success" uk-alert>
        <a class="uk-alert-close" uk-close aria-label="Close success notification"></a>
        <p id="success-message"></p>
    </div>

    <!-- Error Notification -->
    <div wire:ignore id="error-notification" style="display: none;" class="uk-alert-danger" uk-alert>
        <a class="uk-alert-close" uk-close aria-label="Close error notification"></a>
        <p id="error-message"></p>
    </div>

    <!-- Create New Advertisement Button -->
    <a
        href="{{ route('admin.advertisements.create') }}"
        class="uk-button uk-button-primary uk-margin-small-bottom"
        aria-label="Create a new advertisement"
    >
        + New Advertisement
    </a>

    <!-- Advertisements Table -->
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th
                        wire:click="sortBy('title')"
                        class="uk-table-link uk-text-nowrap"
                        role="button"
                        aria-sort="{{ $sortField === 'title' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                    >
                        Title
                        @if ($sortField === 'title')
                            <span uk-icon="{{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                        @endif
                    </th>
                    <th>Type</th>
                    <th>Placement</th>
                    <th
                        wire:click="sortBy('is_active')"
                        class="uk-table-link uk-text-nowrap"
                        role="button"
                        aria-sort="{{ $sortField === 'is_active' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                    >
                        Status
                        @if ($sortField === 'is_active')
                            <span uk-icon="{{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                        @endif
                    </th>
                    <th>Schedule</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ads as $ad)
                    <tr>
                        <td class="uk-text-truncate" style="max-width: 200px;">
                            {{ ($ad->title) }}
                        </td>
                        <td>{{ ucfirst($ad->type) }}</td>
                        <td>{{ e($ad->placement ?? '-') }}</td>
                        <td>
                            @if ($ad->isCurrentlyActive())
                                <span class="uk-label uk-label-success">Active</span>
                            @else
                                <span class="uk-label uk-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if ($ad->starts_at)
                                {{ $ad->starts_at->format('M d, Y H:i') }} -
                                {{ $ad->ends_at?->format('M d, Y H:i') ?? 'No end date' }}
                            @else
                                No schedule
                            @endif
                        </td>
                        <td>
                            <div class="uk-flex uk-flex-middle">
                                <a
                                    href="{{ route('admin.advertisements.edit', $ad->id) }}"
                                    class="uk-button uk-button-small uk-button-default uk-margin-small-right"
                                    aria-label="Edit advertisement: {{ e($ad->title) }}"
                                >
                                    Edit
                                </a>
                                <button
                                    wire:click="delete({{ $ad->id }})"
                                    wire:loading.attr="disabled"
                                    class="uk-button uk-button-small uk-button-danger"
                                    aria-label="Delete advertisement: {{ e($ad->title) }}"
                                >
                                    <span wire:loading.remove>Delete</span>
                                    <span wire:loading>Deleting...</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="uk-text-center">
                            No advertisements found.
                            <a href="{{ route('admin.advertisements.create') }}" class="uk-link">
                                Create one now.
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($ads->hasPages())
        <div class="uk-margin-top">
            {{ $ads->links('vendor.pagination.uikit') }}
        </div>
    @endif


</div>