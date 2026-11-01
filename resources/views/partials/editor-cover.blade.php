{{-- Featured image panel shared by the post and page forms. --}}
@php
    $coverPreview = $cover_image?->isPreviewable() ? $cover_image->temporaryUrl() : $cover_image_url;
@endphp

<details class="ah-panel" open wire:ignore.self>
    <summary>Featured image</summary>
    <div class="ah-panel-body">
        <button type="button" class="ah-cover" onclick="openImagePickerForCover()">
            @if ($coverPreview)
                <img src="{{ str_replace('/storage/storage/', '/storage/', $coverPreview) }}" alt="Featured image preview">
            @else
                <span>Set featured image</span>
            @endif
        </button>

        <div class="ah-cover-actions">
            <button type="button" class="uk-button uk-button-default uk-button-small" onclick="openImagePickerForCover()">Media Library</button>
            <label class="uk-button uk-button-default uk-button-small" for="cover_image">Upload</label>
            <input type="file" id="cover_image" wire:model="cover_image" accept="image/*" aria-label="Upload featured image" hidden>
        </div>

        <p class="ah-panel-hint" wire:loading wire:target="cover_image">Uploading...</p>
    </div>
</details>
