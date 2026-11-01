<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Advertisements</h1>
            <p class="ah-page-sub">{{ $ads->total() }} {{ Str::plural('advertisement', $ads->total()) }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.advertisements.create') }}" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add Advertisement</span>
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
        @if ($ads->total() === 0)
            {{-- Empty State --}}
            <div class="ah-empty ah-empty--section">
                <span class="ah-empty-icon" uk-icon="icon: bolt; ratio: 2" wire:ignore></span>
                <strong class="ah-empty-title">No advertisements yet</strong>
                <p>Add an image or script advertisement to show it on the site.</p>
                <a href="{{ route('admin.advertisements.create') }}" class="uk-button uk-button-primary">Add Advertisement</a>
            </div>
        @else
            {{-- Advertisements Table --}}
            <div
                class="ah-table-wrap"
                wire:loading.class="ah-is-loading"
                wire:target="sortBy,gotoPage,previousPage,nextPage"
            >
                <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                    <thead>
                        <tr>
                            <th class="ah-col-thumb">Preview</th>
                            @include('livewire.admin.media.partials.sort-header', ['column' => 'title', 'label' => 'Title', 'class' => 'ah-col-main', 'sortColumn' => $sortField])
                            <th>Type</th>
                            <th>Placement</th>
                            @include('livewire.admin.media.partials.sort-header', ['column' => 'is_active', 'label' => 'Status', 'sortColumn' => $sortField])
                            <th>Schedule</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ads as $ad)
                            <tr wire:key="ad-{{ $ad->id }}">
                                <td class="ah-col-thumb">
                                    @if ($ad->type === 'image' && $ad->full_image_url)
                                        <img src="{{ $ad->full_image_url }}" alt="" class="ah-thumb" width="72" height="48" loading="lazy" decoding="async">
                                    @else
                                        <span class="ah-thumb ah-thumb--empty">
                                            <span uk-icon="icon: code" wire:ignore></span>
                                        </span>
                                    @endif
                                </td>
                                <td class="ah-col-main">
                                    <a href="{{ route('admin.advertisements.edit', $ad->id) }}" class="ah-table-title ah-clamp-2">{{ $ad->title }}</a>
                                    <span class="ah-table-meta">{{ $ad->slug }}</span>
                                </td>
                                <td>{{ ucfirst($ad->type) }}</td>
                                <td>
                                    @if ($ad->placement)
                                        {{ Str::headline($ad->placement) }}
                                    @else
                                        <span class="ah-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($ad->isCurrentlyActive())
                                        <span class="ah-badge ah-badge--success">Active</span>
                                    @else
                                        <span class="ah-badge ah-badge--danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="ah-col-date">
                                    @if ($ad->starts_at || $ad->ends_at)
                                        {{ $ad->starts_at?->format('M d, Y H:i') ?? 'No start date' }}
                                        <span class="ah-table-meta">to {{ $ad->ends_at?->format('M d, Y H:i') ?? 'No end date' }}</span>
                                    @else
                                        No schedule
                                    @endif
                                </td>
                                <td>
                                    <div class="ah-row-actions">
                                        <a href="{{ route('admin.advertisements.edit', $ad->id) }}">Edit</a>
                                        <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $ad->id }})">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($ads->hasPages())
                <div class="ah-card-foot">
                    {{ $ads->links('partials.admin-pagination') }}
                </div>
            @endif
        @endif
    </div>

    @include('partials.admin-delete-modal', ['title' => 'Delete Advertisement', 'action' => 'delete'])
</div>
