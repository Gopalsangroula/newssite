<div class="ahm">
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Edit Media</h1>
            <p class="ah-page-sub">{{ $originalName }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.media.index') }}" class="uk-button uk-button-default" aria-label="Cancel">Back to library</a>
        </div>
    </div>

    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $flash => $flashClass)
        @if (session($flash))
            <div class="{{ $flashClass }}" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($flash) }}</p>
            </div>
        @endif
    @endforeach

    @if ($uploadError)
        <div class="uk-alert-danger" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ $uploadError }}</p>
        </div>
    @endif

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="uk-form-stacked ahm-edit">
        <section class="ah-card">
            <div class="ah-card-head">
                <h2 class="ah-card-title">File</h2>
            </div>
            <div class="ah-card-body">
                <div class="ahm-field">
                    <label for="file">Replace File (Optional)</label>
                    <input type="file" wire:model="file" id="file" class="uk-input ahm-edit-file"
                           accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,video/mp4,video/webm,audio/mpeg"
                           aria-label="Upload media file">
                    @error('file')
                        <span class="ahm-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="ahm-field">
                    <label for="customFilename">Custom Filename</label>
                    <input type="text" wire:model="customFilename" id="customFilename"
                           class="uk-input" placeholder="Enter custom filename" aria-label="Custom filename">
                    @error('customFilename')
                        <span class="ahm-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="ahm-field">
                    <label for="imgDescription">Image Description</label>
                    <textarea wire:model="imgDescription" id="imgDescription" rows="4"
                              class="uk-textarea" placeholder="Enter image description"
                              aria-label="Image description"></textarea>
                    @error('imgDescription')
                        <span class="ahm-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="ah-card-foot ahm-edit-actions">
                <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" aria-label="Update media">
                    Update Media
                </button>
                <a href="{{ route('admin.media.index') }}" class="uk-button uk-button-default" aria-label="Cancel">Cancel</a>
            </div>
        </section>

        <section class="ah-card">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Preview</h2>
            </div>
            <div class="ahm-edit-preview">
                @if ($preview && str_starts_with((string) $mimeType, 'image/'))
                    <img src="{{ $preview }}" alt="{{ $imgDescription ?: 'Media preview' }}">
                @else
                    @include('livewire.admin.media.partials.icon', ['name' => (new \App\Models\Media(['mime_type' => $mimeType]))->kind])
                @endif
            </div>
            <div class="ah-card-body">
                <dl class="ahm-meta">
                    <div>
                        <dt>Original Name</dt>
                        <dd>{{ $originalName }}</dd>
                    </div>
                    <div>
                        <dt>Type</dt>
                        <dd>{{ $mimeType }}</dd>
                    </div>
                    <div>
                        <dt>Size</dt>
                        <dd>{{ $formatBytes($size) }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </form>
</div>
