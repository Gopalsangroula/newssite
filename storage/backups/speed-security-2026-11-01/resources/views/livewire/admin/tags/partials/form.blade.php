{{-- The tag fields, shared by tags/create and tags/edit. --}}
<div class="ah-card-body">
    <div class="ah-form-grid ah-form-grid--single">
        {{-- Name --}}
        <div class="ah-field">
            <label for="name" class="uk-form-label">Name <span class="ah-required">*</span></label>
            <input
                id="name"
                type="text"
                wire:model.live.debounce.400ms="name"
                class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}"
                placeholder="Enter tag name"
            >
            @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
        </div>

        {{-- Slug --}}
        <div class="ah-field">
            <label for="slug" class="uk-form-label">Slug <span class="ah-required">*</span></label>
            <input
                id="slug"
                type="text"
                wire:model="slug"
                class="uk-input {{ $errors->has('slug') ? 'uk-form-danger' : '' }}"
                placeholder="Auto-generated or edit manually"
            >
            <div class="ah-field-hint">Generated from the name.</div>
            @error('slug') <div class="ah-field-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
