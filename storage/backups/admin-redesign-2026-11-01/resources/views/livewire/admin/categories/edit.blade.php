<div>
    <h2 class="uk-heading-line"><span>Edit Category</span></h2>

    {{-- Flash Message --}}
    @if (session('message'))
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="update" class="uk-form-stacked uk-margin">

        {{-- Name --}}
        <div class="uk-margin">
            <label class="uk-form-label">Name</label>
            <div class="uk-form-controls">
                <input wire:model="name" wire:keyup="generateSlug" class="uk-input" type="text" placeholder="Enter category name">
                @error('name') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Slug --}}
        <div class="uk-margin">
            <label class="uk-form-label">Slug</label>
            <div class="uk-form-controls">
                <input wire:model="slug" class="uk-input" type="text" placeholder="Auto-generated or edit manually">
                @error('slug') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Parent Category --}}
        <div class="uk-margin">
            <label class="uk-form-label">Parent Category</label>
            <div class="uk-form-controls">
                <select wire:model="parent_id" class="uk-select">
                    <option value="">-- None --</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="uk-margin">
            <button type="submit" class="uk-button uk-button-primary">Update</button>
            <a href="{{ route('admin.categories.index') }}" class="uk-button uk-button-default">Cancel</a>
        </div>
    </form>
</div>
