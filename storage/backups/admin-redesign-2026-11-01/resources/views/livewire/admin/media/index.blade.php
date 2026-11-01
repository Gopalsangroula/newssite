<div class="uk-container uk-margin-large">
    <h2 class="text-2xl font-bold uk-margin-bottom ah-admin-title uk-text-center">Media Library</h2>

    {{-- Flash Messages --}}
    @if (session('message'))
        <div class="uk-alert-success" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="uk-alert-danger" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Search and Filters --}}
    <div class="uk-margin-bottom uk-flex uk-flex-between uk-flex-wrap">
        <div class="uk-width-1-2@m uk-width- 1-1@s">
            <div class="uk-inline uk-width-1-1">
                <span class="uk-form-icon" uk-icon="icon: search"></span>
                <input wire:model.live.debounce.500ms="search" class="uk-input" type="text" 
                       placeholder="Search by name or type..." aria-label="Search media">
            </div>
        </div>
        <div class="uk-width-1-4@m uk-width-1-2@s">
            <select wire:model.live="perPage" class="uk-select" aria-label="Items per page">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
    </div>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-middle uk-table-responsive">
            <thead>
                <tr>
                    <th class="uk-width-small">
                        <a href="#" wire:click="sortBy('id')" class="uk-link-reset">
                            Preview
                            @if($sortBy === 'id')
                                <span uk-icon="icon: {{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="#" wire:click="sortBy('original_name')" class="uk-link-reset">
                            Original Name
                            @if($sortBy === 'original_name')
                                <span uk-icon="icon: {{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="#" wire:click="sortBy('size')" class="uk-link-reset">
                            Size
                            @if($sortBy === 'size')
                                <span uk-icon="icon: {{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="#" wire:click="sortBy('mime_type')" class="uk-link-reset">
                            Type
                            @if($sortBy === 'mime_type')
                                <span uk-icon="icon: {{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="#" wire:click="sortBy('created_at')" class="uk-link-reset">
                            Date
                            @if($sortBy === 'created_at')
                                <span uk-icon="icon: {{ $sortDirection === 'asc' ? 'triangle-up' : 'triangle-down' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th class="uk-table-shrink">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mediaItems as $media)
                    <tr>
                        <td class="uk-text-center">
                            <img src="{{ $media->url }}" width="60" alt="{{ $media->original_name }}" class="uk-border-rounded">
                        </td>
                        <td>{{ Str::limit($media->original_name, 30) }}</td>
                        <td>{{ number_format($media->size / 1024, 2) }} KB</td>
                        <td>{{ $media->mime_type }}</td>
                        <td>{{ $media->created_at->format('Y-m-d H:i') }}</td>
                        <td class="uk-flex uk-flex-nowrap">
                            <a href="{{ route('admin.media.edit', $media->id) }}" 
                               class="uk-button uk-button-default uk-margin-small-right"
                               aria-label="Edit media">Edit</a>
                            <button wire:click="deleteMedia({{ $media->id }})" 
                                    class="uk-button uk-button-danger"
                                    wire:confirm="Are you sure you want to delete this media?"
                                    aria-label="Delete media">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="uk-text-center">No media items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="uk-margin-top">
        {{ $mediaItems->links() }}
    </div>
</div>