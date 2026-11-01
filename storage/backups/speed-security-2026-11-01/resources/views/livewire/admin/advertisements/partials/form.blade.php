{{-- The advertisement fields, shared by advertisements/create and advertisements/edit. --}}
@php
    $placements = [
        'sidebar' => 'Sidebar',
        'sidebar_right' => 'Sidebar Right',
        'sidebar_bottom' => 'Sidebar Bottom',
        'header' => 'Header',
        'committee' => 'committee top ads',
        'photo' => 'photo top ads',
        'footer' => 'Footer',
    ];
@endphp

{{-- Details --}}
<section class="ah-card ah-form-card">
    <div class="ah-card-head">
        <h2 class="ah-card-title">Advertisement Details</h2>
    </div>
    <div class="ah-card-body">
        <div class="ah-form-grid">
            <div class="ah-field">
                <label for="title" class="uk-form-label">Advertisement Title <span class="ah-required">*</span></label>
                <input id="title" type="text" wire:model.live.debounce.400ms="title" class="uk-input {{ $errors->has('title') ? 'uk-form-danger' : '' }}" placeholder="Enter advertisement title">
                @error('title') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>

            <div class="ah-field">
                <label for="slug" class="uk-form-label">Slug <span class="ah-required">*</span></label>
                <input id="slug" type="text" wire:model.live.blur="slug" class="uk-input {{ $errors->has('slug') ? 'uk-form-danger' : '' }}" placeholder="Auto-generated from title (e.g., ad-title)">
                @error('slug') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>

            <div class="ah-field">
                <label for="type" class="uk-form-label">Type <span class="ah-required">*</span></label>
                <select id="type" wire:model.live="type" class="uk-select {{ $errors->has('type') ? 'uk-form-danger' : '' }}">
                    <option value="image">Image</option>
                    <option value="script">Script</option>
                </select>
                @error('type') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>

            <div class="ah-field">
                <label for="placement" class="uk-form-label">Placement</label>
                <select id="placement" wire:model="placement" class="uk-select {{ $errors->has('placement') ? 'uk-form-danger' : '' }}">
                    <option value="">Select placement</option>
                    @foreach ($placements as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                    {{-- Edit used to take free text, so keep a value that is not in the list --}}
                    @if ($placement && ! array_key_exists($placement, $placements))
                        <option value="{{ $placement }}">{{ $placement }}</option>
                    @endif
                </select>
                @error('placement') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</section>

{{-- Content --}}
<section class="ah-card ah-form-card">
    <div class="ah-card-head">
        <h2 class="ah-card-title">Content</h2>
    </div>
    <div class="ah-card-body">
        <div class="ah-form-grid ah-form-grid--single">
            @if ($type === 'script')
                <div class="ah-field" wire:key="ad-script-fields">
                    <label for="content" class="uk-form-label">Script Content <span class="ah-required">*</span></label>
                    <textarea id="content" wire:model.live.blur="content" rows="6" class="uk-textarea {{ $errors->has('content') ? 'uk-form-danger' : '' }}" placeholder="Paste your ad script here (e.g., JavaScript or HTML)"></textarea>
                    @error('content') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            @endif

            @if ($type === 'image')
                <div class="ah-field" wire:key="ad-image-fields">
                    <label for="image" class="uk-form-label">Image <span class="ah-required">*</span></label>
                    <input id="image" type="file" wire:model="image" accept="image/*" class="ah-file">
                    <div class="ah-field-hint">Up to 2MB.</div>
                    @error('image') <div class="ah-field-error">{{ $message }}</div> @enderror

                    <div wire:loading wire:target="image" class="ah-field-hint">Uploading...</div>

                    @if ($image && $image->isPreviewable())
                        <div class="ah-image-preview ah-image-preview--wide">
                            <img src="{{ $image->temporaryUrl() }}" alt="New image preview">
                            <span>New image preview</span>
                        </div>
                    @elseif ($existingImage ?? null)
                        <div class="ah-image-preview ah-image-preview--wide">
                            <img src="{{ Str::startsWith($existingImage, ['http://', 'https://']) ? $existingImage : asset('storage/' . $existingImage) }}" alt="Current image">
                            <span>Current image</span>
                        </div>
                    @endif
                </div>

                <div class="ah-field" wire:key="ad-target-field">
                    <label for="target_url" class="uk-form-label">Target URL</label>
                    <input id="target_url" type="url" wire:model.live.blur="target_url" class="uk-input {{ $errors->has('target_url') ? 'uk-form-danger' : '' }}" placeholder="e.g., https://example.com">
                    @error('target_url') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Schedule --}}
<section class="ah-card ah-form-card">
    <div class="ah-card-head">
        <h2 class="ah-card-title">Schedule</h2>
    </div>
    <div class="ah-card-body">
        <div class="ah-form-grid">
            <div class="ah-field">
                <label for="starts_at" class="uk-form-label">Starts At</label>
                <input id="starts_at" type="datetime-local" wire:model.live.blur="starts_at" class="uk-input {{ $errors->has('starts_at') ? 'uk-form-danger' : '' }}">
                @error('starts_at') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>

            <div class="ah-field">
                <label for="ends_at" class="uk-form-label">Ends At</label>
                <input id="ends_at" type="datetime-local" wire:model.live.blur="ends_at" class="uk-input {{ $errors->has('ends_at') ? 'uk-form-danger' : '' }}">
                @error('ends_at') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>

            <div class="ah-field ah-field--full">
                <label class="ah-option">
                    <input type="checkbox" wire:model.live="is_active" class="uk-checkbox">
                    <span>Active</span>
                </label>
                @error('is_active') <div class="ah-field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</section>
