<div class="uk-container uk-margin-large">
    <h2 class="text-2xl font-bold uk-margin-bottom ah-admin-title uk-text-center">Edit Media</h2>

    {{-- Flash Messages --}}
    @if (session('message'))
        <div class="uk-alert-success" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="uk-alert-danger" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($uploadError)
        <div class="uk-alert-danger" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ $uploadError }}</p>
        </div>
    @endif

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="uk-form-stacked">
        <div class="uk-grid uk-grid-medium" uk-grid>
            <div class="uk-width-1-2@m uk-width-1-1@s">
                <div class="uk-margin">
                    <label class="uk-form-label" for="file">Replace File (Optional)</label>
                    <input type="file" wire:model="file" id="file" class="uk-input" 
                           accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,video/mp4,video/webm,audio/mpeg"
                           aria-label="Upload media file">
                    @error('file') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="customFilename">Custom Filename</label>
                    <input type="text" wire:model.debounce.500ms="customFilename" id="customFilename" 
                           class="uk-input" placeholder="Enter custom filename" aria-label="Custom filename">
                    @error('customFilename') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="imgDescription">Image Description</label>
                    <textarea wire:model.debounce.500ms="imgDescription" id="imgDescription" 
                              class="uk-textarea" placeholder="Enter image description" 
                              aria-label="Image description"></textarea>
                    @error('imgDescription') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="uk-width-1-2@m uk-width-1-1@s">
                <div class="uk-margin">
                    <label class="uk-form-label">Preview</label>
                    <div class="uk-margin-small-top">
                        @if ($preview && str_starts_with($mimeType, 'image/'))
                            <img src="{{ $preview }}" class="uk-width-medium uk-border-rounded" 
                                 alt="{{ $imgDescription ?: 'Media preview' }}">
                        @else
                            <img src="{{ $preview }}" class="uk-width-small" alt="File icon">
                        @endif
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label">File Details</label>
                    <ul class="uk-list uk-list-divider">
                        <li><strong>Original Name:</strong> {{ $originalName }}</li>
                        <li><strong>Type:</strong> {{ $mimeType }}</li>
                        <li><strong>Size:</strong> {{ $formatBytes($size) }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="uk-margin-top uk-flex uk-flex-center">
            <button type="submit" class="uk-button uk-button-primary" 
                    wire:loading.attr="disabled" aria-label="Update media">
                Update Media
            </button>
            <a href="{{ route('admin.media.index') }}" 
               class="uk-button uk-button-default uk-margin-small-left" 
               aria-label="Cancel">Cancel</a>
        </div>
    </form>
</div>