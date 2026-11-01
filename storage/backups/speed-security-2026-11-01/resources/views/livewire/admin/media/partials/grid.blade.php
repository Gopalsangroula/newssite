<div class="ahm-grid">
    @foreach ($mediaItems as $media)
        @php($name = $media->original_name ?: $media->filename)

        <div class="ahm-tile ahm-item {{ $media->kind === 'image' ? '' : 'ahm-tile--file' }}"
             wire:key="tile-{{ $media->id }}"
             data-media-id="{{ $media->id }}"
             data-media-name="{{ $name }}"
             data-media-attached="{{ $media->mediable_type && $media->mediable_id ? 1 : 0 }}"
             :class="{ 'is-selected': isSelected({{ $media->id }}) }">
            <button type="button" class="ahm-tile-open" title="{{ $name }}" aria-label="View details of {{ $name }}"
                    x-on:click="tileClick($el, {{ $media->id }})">
                <span class="ahm-tile-icon">
                    @include('livewire.admin.media.partials.icon', ['name' => $media->kind])
                </span>
                @if ($media->kind === 'image')
                    <img src="{{ $media->url }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                @endif
                <span class="ahm-tile-name">{{ $name }}</span>
            </button>

            @if ($canDelete)
                <label class="ahm-tile-check" title="Select {{ $name }}">
                    <input type="checkbox" class="uk-checkbox" aria-label="Select {{ $name }}"
                           :checked="isSelected({{ $media->id }})"
                           x-on:change="toggle($el.closest('.ahm-item'))">
                </label>
            @endif
        </div>
    @endforeach
</div>
