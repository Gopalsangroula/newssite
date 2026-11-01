<div class="ah-table-wrap">
    <table class="uk-table uk-table-divider uk-table-middle ah-table ahm-table">
        <thead>
            <tr>
                @if ($canDelete)
                    <th class="ahm-col-check">
                        <input type="checkbox" class="uk-checkbox" aria-label="Select all on this page"
                               :checked="allSelected(@js($mediaItems->pluck('id')))"
                               x-on:change="$el.checked ? selectPage() : deselectPage()">
                    </th>
                @endif
                <th class="ahm-col-thumb" aria-label="Thumbnail"></th>
                @include('livewire.admin.media.partials.sort-header', ['column' => 'original_name', 'label' => 'File name'])
                @include('livewire.admin.media.partials.sort-header', ['column' => 'mime_type', 'label' => 'Type', 'class' => 'ahm-col-extra'])
                @include('livewire.admin.media.partials.sort-header', ['column' => 'size', 'label' => 'Size', 'class' => 'ahm-col-extra'])
                <th class="ahm-col-extra">Attached to</th>
                @include('livewire.admin.media.partials.sort-header', ['column' => 'created_at', 'label' => 'Date', 'class' => 'ahm-col-extra'])
            </tr>
        </thead>
        <tbody>
            @foreach ($mediaItems as $media)
                @php($name = $media->original_name ?: $media->filename)

                <tr class="ahm-item"
                    wire:key="row-{{ $media->id }}"
                    data-media-id="{{ $media->id }}"
                    data-media-name="{{ $name }}"
                    data-media-attached="{{ $media->mediable_type && $media->mediable_id ? 1 : 0 }}"
                    :class="{ 'is-selected': isSelected({{ $media->id }}) }">
                    @if ($canDelete)
                        <td class="ahm-col-check">
                            <input type="checkbox" class="uk-checkbox" aria-label="Select {{ $name }}"
                                   :checked="isSelected({{ $media->id }})"
                                   x-on:change="toggle($el.closest('.ahm-item'))">
                        </td>
                    @endif
                    <td class="ahm-col-thumb">
                        <span class="ah-thumb ah-thumb--empty">
                            @include('livewire.admin.media.partials.icon', ['name' => $media->kind])
                        </span>
                        @if ($media->kind === 'image')
                            <img class="ah-thumb" src="{{ $media->url }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                        @endif
                    </td>
                    <td class="ahm-col-name">
                        <button type="button" class="ahm-link ah-table-title" x-on:click="show({{ $media->id }})">{{ $name }}</button>
                        <span class="ah-table-meta ahm-row-meta">
                            {{ $media->mime_type }} · {{ $media->formatted_size }} · {{ $media->created_at?->format('M j, Y') }}
                        </span>
                        <div class="ah-row-actions">
                            <button type="button" x-on:click="show({{ $media->id }})">Details</button>
                            @if ($canEdit)
                                <a href="{{ route('admin.media.edit', $media->id) }}">Edit</a>
                            @endif
                            <a href="{{ $media->url }}" target="_blank" rel="noopener">View</a>
                            @if ($canDelete)
                                <button type="button" class="ah-action-danger" x-on:click="askDelete($el.closest('.ahm-item'))">Delete</button>
                            @endif
                        </div>
                    </td>
                    <td class="ahm-col-extra">
                        <span class="ah-badge ah-badge--info">{{ ucfirst($media->kind) }}</span>
                        <span class="ah-table-meta">{{ $media->mime_type }}</span>
                    </td>
                    <td class="ahm-col-extra ahm-nowrap">{{ $media->formatted_size }}</td>
                    <td class="ahm-col-extra">
                        @include('livewire.admin.media.partials.attached', ['media' => $media])
                    </td>
                    <td class="ahm-col-extra ahm-nowrap">{{ $media->created_at?->format('M j, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
