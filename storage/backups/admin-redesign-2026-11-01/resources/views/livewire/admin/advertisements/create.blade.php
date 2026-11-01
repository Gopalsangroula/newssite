<div>
    <h2 class="uk-heading-line"><span>Create Advertisement</span></h2>

    <!-- Success Notification -->
    @if (session('message'))
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close aria-label="Close success notification"></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <!-- General Error Notification -->
    @if ($errors->has('general'))
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close aria-label="Close error notification"></a>
            <p>{{ $errors->first('general') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="save" class="uk-form-stacked" enctype="multipart/form-data">
        <!-- Title -->
        <div class="uk-margin">
            <label class="uk-form-label" for="title">Advertisement Title <span class="uk-text-danger" aria-hidden="true">*</span></label>
            <div class="uk-form-controls">
                <input
                    type="text"
                    class="uk-input"
                    id="title"
                    wire:model.live="title"
                    placeholder="Enter advertisement title"
                    aria-required="true"
                    aria-describedby="title-error"
                >
                @error('title')
                    <span id="title-error" class="uk-text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Slug -->
        <div class="uk-margin">
            <label class="uk-form-label" for="slug">Slug <span class="uk-text-danger" aria-hidden="true">*</span></label>
            <div class="uk-form-controls">
                <input
                    type="text"
                    class="uk-input"
                    id="slug"
                    wire:model.live="slug"
                    placeholder="Auto-generated from title (e.g., ad-title)"
                    aria-required="true"
                    aria-describedby="slug-error"
                >
                @error('slug')
                    <span id="slug-error" class="uk-text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Type -->
        <div class="uk-margin">
            <label class="uk-form-label" for="type">Type <span class="uk-text-danger" aria-hidden="true">*</span></label>
            <div class="uk-form-controls">
                <select
                    class="uk-select"
                    id="type"
                    wire:model.live="type"
                    aria-required="true"
                    aria-describedby="type-error"
                >
                    <option value="image">Image</option>
                    <option value="script">Script</option>
                </select>
                @error('type')
                    <span id="type-error" class="uk-text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Placement -->
        <div class="uk-margin">
              <label class="uk-form-label" for="placement">Placement</label>
                <select class="uk-select" wire:model="placement" id="placement" aria-label="Select placement">
                    <option value="">-- Select Placement --</option>
                    <option value="sidebar">Sidebar</option>
                    <option value="sidebar_right">Sidebar Right</option>
                    <option value="sidebar_bottom">Sidebar Bottom</option>
                    <option value="header">Header</option>
                    <option value="comitteee">comittee top ads</option>
                    <option value="footer">Footer</option>
                </select>
                @error('placement')
                    <span class="uk-text-danger uk-text-small">{{ $message }}</span>
                @enderror

        </div>

        <!-- Script Content (if type is script) -->
        @if ($type === 'script')
            <div class="uk-margin">
                <label class="uk-form-label" for="content">Script Content <span class="uk-text-danger" aria-hidden="true">*</span></label>
                <div class="uk-form-controls">
                    <textarea
                        class="uk-textarea"
                        id="content"
                        wire:model.live="content"
                        placeholder="Paste your ad script here (e.g., JavaScript or HTML)"
                        rows="5"
                        aria-required="true"
                        aria-describedby="content-error"
                    ></textarea>
                    @error('content')
                        <span id="content-error" class="uk-text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        @endif

        <!-- Image and Target URL (if type is image) -->
        @if ($type === 'image')
            <div class="uk-margin">
                <label class="uk-form-label" for="image">Image <span class="uk-text-danger" aria-hidden="true">*</span></label>
                <div class="uk-form-controls">
                    <input
                        type="file"
                        class="uk-input"
                        id="image"
                        wire:model="image"
                        accept="image/*"
                        aria-required="true"
                        aria-describedby="image-error"
                    >
                    @error('image')
                        <span id="image-error" class="uk-text-danger">{{ $message }}</span>
                    @enderror
                    @if ($imagePreview)
                        <div class="uk-margin-small-top">
                            <img
                                src="{{ $imagePreview }}"
                                class="uk-thumbnail"
                                style="max-width: 200px;"
                                alt="Image preview"
                            >
                        </div>
                    @endif
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="target_url">Target URL</label>
                <div class="uk-form-controls">
                    <input
                        type="url"
                        class="uk-input"
                        id="target_url"
                        wire:model.live="target_url"
                        placeholder="e.g., https://example.com"
                        aria-describedby="target_url-error"
                    >
                    @error('target_url')
                        <span id="target_url-error" class="uk-text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        @endif

        <!-- Active Checkbox -->
        <div class="uk-margin">
            <div class="uk-form-controls">
                <label class="uk-form-label">
                    <input
                        type="checkbox"
                        class="uk-checkbox"
                        wire:model.live="is_active"
                        aria-describedby="is_active-error"
                    >
                    Active
                </label>
                @error('is_active')
                    <span id="is_active-error" class="uk-text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Starts At -->
        <div class="uk-margin">
            <label class="uk-form-label" for="starts_at">Starts At</label>
            <div class="uk-form-controls">
                <input
                    type="datetime-local"
                    class="uk-input"
                    id="starts_at"
                    wire:model.live="starts_at"
                    aria-describedby="starts_at-error"
                >
                @error('starts_at')
                    <span id="starts_at-error" class="uk-text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Ends At -->
        <div class="uk-margin">
            <label class="uk-form-label" for="ends_at">Ends At</label>
            <div class="uk-form-controls">
                <input
                    type="datetime-local"
                    class="uk-input"
                    id="ends_at"
                    wire:model.live="ends_at"
                    aria-describedby="ends_at-error"
                >
                @error('ends_at')
                    <span id="ends_at-error" class="uk-text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Form Actions -->
        <div class="uk-margin">
            <button
                type="submit"
                class="uk-button uk-button-primary"
                wire:loading.attr="disabled"
                wire:loading.class="uk-button-disabled"
            >
                <span wire:loading.remove>Save Advertisement</span>
                <span wire:loading>Saving...</span>
            </button>
            <a
                href="{{ route('admin.advertisements.index') }}"
                class="uk-button uk-button-default"
                aria-label="Cancel and return to advertisements list"
            >Cancel</a>
        </div>
    </form>

    <!-- Handle Advertisement Created Event -->

        <script>
            $wire.on('advertisementCreated', ({ message }) => {
                UIkit.notification({
                    message: message,
                    status: 'success',
                    pos: 'top-right'
                });
            });
        </script>

</div>