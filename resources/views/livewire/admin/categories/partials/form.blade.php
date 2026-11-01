{{-- The category fields, shared by categories/create and categories/edit. --}}
<div class="ah-card-body">
    <div class="ah-form-grid ah-form-grid--single">
        {{-- Name --}}
        <div class="ah-field">
            <label for="name" class="uk-form-label">Name <span class="ah-required">*</span></label>
            <input
                id="name"
                type="text"
                wire:model="name"
                wire:keyup.debounce.400ms="generateSlug"
                class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}"
                placeholder="Enter category name"
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
            <div class="ah-field-hint">Generated from the name. Lowercase letters, numbers and hyphens only.</div>
            @error('slug') <div class="ah-field-error">{{ $message }}</div> @enderror
        </div>

        {{-- Parent Category --}}
        <div class="ah-field">
            <label for="parent_id" class="uk-form-label">Parent Category</label>
            <select id="parent_id" wire:model="parent_id" class="uk-select {{ $errors->has('parent_id') ? 'uk-form-danger' : '' }}">
                <option value="">None (top level)</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            @error('parent_id') <div class="ah-field-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
