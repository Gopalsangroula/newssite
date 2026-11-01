<div>
    <h2 class="uk-heading-line"><span>Edit Advertisement</span></h2>

    @if (session('message'))
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="update" class="uk-form-stacked" enctype="multipart/form-data">

        <div class="uk-margin">
            <label class="uk-form-label">Title</label>
            <div class="uk-form-controls">
                <input type="text" class="uk-input" wire:model="title" placeholder="Ad title">
                @error('title') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Type</label>
            <div class="uk-form-controls">
                <select class="uk-select" wire:model="type">
                    <option value="image">Image</option>
                    <option value="script">Script</option>
                </select>
                @error('type') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Placement</label>
            <div class="uk-form-controls">
                <input type="text" class="uk-input" wire:model="placement" placeholder="e.g. sidebar, header">
                @error('placement') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        @if ($type === 'script')
            <div class="uk-margin">
                <label class="uk-form-label">Script Content</label>
                <div class="uk-form-controls">
                    <textarea class="uk-textarea" wire:model="content" placeholder="Paste ad script here"></textarea>
                    @error('content') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        @if ($type === 'image')
            <div class="uk-margin">
                <label class="uk-form-label">Image</label>
                <div class="uk-form-controls">
                    <input type="file" wire:model="image">
                    @error('image') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>
                @if ($image)
                    <div class="uk-margin-small-top">
                        <img src="{{ $image->temporaryUrl() }}" class="uk-thumbnail" style="max-width:200px;">
                    </div>
                @elseif ($existingImage)
                    <div class="uk-margin-small-top">
                        <img src="{{ asset('storage/'.$existingImage) }}" class="uk-thumbnail" style="max-width:200px;">
                    </div>
                @endif
            </div>

            <div class="uk-margin">
                <label class="uk-form-label">Target URL</label>
                <div class="uk-form-controls">
                    <input type="text" class="uk-input" wire:model="target_url" placeholder="https://example.com">
                    @error('target_url') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        <div class="uk-margin">
            <label class="uk-form-label">Active</label>
            <div class="uk-form-controls">
                <input type="checkbox" wire:model="is_active" class="uk-checkbox"> Yes
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Starts At</label>
            <div class="uk-form-controls">
                <input type="datetime-local" class="uk-input" wire:model="starts_at">
                @error('starts_at') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label">Ends At</label>
            <div class="uk-form-controls">
                <input type="datetime-local" class="uk-input" wire:model="ends_at">
                @error('ends_at') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="uk-margin">
            <button type="submit" class="uk-button uk-button-primary">Update Advertisement</button>
            <a href="{{ route('admin.advertisements.index') }}" class="uk-button uk-button-default">Cancel</a>
        </div>

    </form>
</div>
