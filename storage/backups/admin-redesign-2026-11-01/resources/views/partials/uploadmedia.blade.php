<div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
        <h2 class="uk-card-title">Upload Files</h2>

        <!-- Drag and Drop Area -->
        <div class="uk-margin">
            <input 
                type="file" 
                wire:model="files" 
                multiple 
                id="file-upload"
                class="uk-hidden"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.mp4,.webm,.mp3"
                aria-describedby="file-upload-help"
            >
            <label 
                for="file-upload"
                x-data="{ isDragging: false }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                class="uk-form-controls uk-display-block uk-text-center uk-padding uk-border-dashed uk-border-rounded"
                :class="{ 'uk-border-primary uk-background-muted': isDragging, 'uk-border-danger uk-background-danger-light': {{ $errors->has('files.*') ? 'true' : 'false' }} }"
            >
                <div class="uk-flex uk-flex-column uk-flex-center">
                    <div class="uk-flex uk-flex-column">
                        <p><span uk-icon="icon: cloud-upload; ratio: 2.5" class="uk-margin-small-bottom uk-text-middle"></span></p>
                        <span class="uk-text-middle">Drag and drop files here or click to browse</span>
                        <span class="uk-text-small uk-text-muted uk-margin-small-top">Supported formats: Images, PDFs, Documents, Videos, Audio (Max 20MB)</span>
                    </div>
                </div>
                <input type="file" x-ref="fileInput" class="uk-hidden" multiple>
            </label>
            <div id="file-upload-help" class="uk-hidden">
                Upload files in supported formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, MP4, WEBM, MP3. Maximum size is 20MB per file.
            </div>
            @error('files.*') 
                <span class="uk-text-danger uk-text-small uk-display-block uk-margin-small-top">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Progress Bar -->
 

        <!-- Preview Section -->
        @if (!empty($files))
            <div class="uk-grid uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l uk-margin" uk-grid>
                @foreach ($files as $index => $file)
                    <div x-data="{ editing: false }">
                        <div class="uk-card uk-card-default uk-card-small uk-card-hover">
                            @if (isset($previews[$index]) && Str::startsWith($file->getMimeType(), 'image/'))
                                <div class="uk-card-media-top">
                                    <img src="{{ $previews[$index] }}" alt="{{ $file->getClientOriginalName() }}" class="uk-object-contain" style="height: 192px; width: 100%;" loading="lazy">
                                </div>
                            @else
                                <div class="uk-card-media-top uk-flex uk-flex-column uk-flex-center uk-flex-middle uk-background-muted" style="height: 192px;">
                                    <img src="{{ $previews[$index] ?? asset('icons/file-icon.svg') }}" alt="{{ $this->getFileType($file->getClientOriginalExtension()) }} icon" class="uk-height-small uk-margin-small-bottom">
                                    <span class="uk-text-small uk-text-muted uk-text-center uk-padding-small uk-text-truncate" title="{{ $file->getClientOriginalName() }}">{{ Str::limit($file->getClientOriginalName(), 20) }}</span>
                                </div>
                            @endif

                            <div class="uk-card-body uk-padding-small">
                                <div class="uk-margin-small">
                                    <div class="uk-flex uk-flex-middle">
                                        <input 
                                            type="text" 
                                            wire:model.live="customFilenames.{{ $index }}" 
                                            class="uk-input uk-form-small" 
                                            placeholder="Custom Filename" 
                                            aria-label="Custom filename for {{ $file->getClientOriginalName() }}"
                                        >
                                    </div>
                                    @error('customFilenames.' . $index) 
                                        <span class="uk-text-danger uk-text-small uk-display-block uk-margin-small-top">{{ $message }}</span> 
                                    @enderror
                                    @if (Str::startsWith($file->getMimeType(), 'image/'))
                                        <div class="uk-flex uk-flex-middle uk-margin-small-top">
                                            <input 
                                                type="text" 
                                                wire:model.live="imgDescriptions.{{ $index }}" 
                                                class="uk-input uk-form-small" 
                                                placeholder="Image Description" 
                                                aria-label="Image description for {{ $file->getClientOriginalName() }}"
                                            >
                                        </div>
                                        @error('imgDescriptions.' . $index) 
                                            <span class="uk-text-danger uk-text-small uk-display-block uk-margin-small-top">{{ $message }}</span> 
                                        @enderror
                                    @endif
                                </div>

                                <div class="uk-text-small uk-text-muted">
                                    <p class="uk-text-truncate" title="{{ $file->getClientOriginalName() }}">
                                        <span class="uk-text-bold">Original:</span> {{ Str::limit($file->getClientOriginalName(), 25) }}
                                    </p>
                                    <p>
                                        <span class="uk-text-bold">Size:</span> {{ $this->formatBytes($file->getSize()) }}
                                    </p>
                                    <p>
                                        <span class="uk-text-bold">Type:</span> {{ $this->getFileType($file->getClientOriginalExtension()) }}
                                    </p>
                                </div>

                                <button 
                                    wire:click="removeFile({{ $index }})" 
                                    class="uk-button uk-button-danger uk-button-small uk-margin-small-top uk-width-1-1"
                                    aria-label="Remove {{ $file->getClientOriginalName() }}"
                                >
                                    <span uk-icon="icon: trash"></span> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <button 
            wire:click="save" 
            wire:loading.attr="disabled"
            class="uk-button uk-button-primary uk-width-1-1@s uk-width-auto@m uk-margin-top"
            :disabled="{{ empty($files) ? 'true' : 'false' }}"
            aria-label="Upload selected files"
        >
            <span wire:loading.remove>Upload Files</span>
            <span wire:loading>
                <span uk-spinner="ratio: 0.6" class="uk-margin-small-right"></span> Uploading...
            </span>
        </button>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('resetFileInput', () => {
                const fileInputs = document.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => input.value = '');
            });

            Livewire.on('upload-progress', (event) => {
                console.log('Upload progress:', event.progress);
            });

            Livewire.on('upload-failed', (event) => {
                console.error('Upload errors:', event.errors);
                UIkit.notification({
                    message: 'Upload failed: ' + event.errors.join(', '),
                    status: 'danger',
                    pos: 'top-right'
                });
            });

            Livewire.on('media-deleted', () => {
                UIkit.notification({
                    message: 'File deleted successfully!',
                    status: 'success',
                    pos: 'top-right'
                });
            });

            Livewire.on('upload-complete', () => {
                UIkit.notification({
                    message: 'Files uploaded successfully!',
                    status: 'success',
                    pos: 'top-right'
                });
            });
        });
    </script>