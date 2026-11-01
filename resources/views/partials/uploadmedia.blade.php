{{-- The view of the Uploader component, used by the media library and the Add Media page. --}}
<div class="ahm-uploader"
     x-data="{
        dragging: false,
        uploading: false,
        failed: false,
        progress: 0,
        drop(event) {
            this.dragging = false;

            if (event.dataTransfer.files.length) {
                this.$refs.input.files = event.dataTransfer.files;
                this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
     }"
     x-on:upload-complete.window="
        $refs.input.value = '';
        window.UIkit && UIkit.notification({
            message: $event.detail.count === 1 ? 'File uploaded successfully.' : $event.detail.count + ' files uploaded successfully.',
            status: 'success',
            pos: 'top-right',
        });
     ">
    <input type="file"
           wire:model="files"
           multiple
           id="file-upload-{{ $this->getId() }}"
           class="ahm-file-input"
           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.mp4,.webm,.mp3"
           aria-describedby="file-upload-help-{{ $this->getId() }}"
           x-ref="input"
           x-on:livewire-upload-start="uploading = true; failed = false; progress = 0"
           x-on:livewire-upload-progress="progress = $event.detail.progress"
           x-on:livewire-upload-finish="uploading = false"
           x-on:livewire-upload-cancel="uploading = false"
           x-on:livewire-upload-error="uploading = false; failed = true; $refs.input.value = ''">

    <label for="file-upload-{{ $this->getId() }}"
           class="ahm-dropzone"
           :class="{ 'is-dragging': dragging }"
           x-on:dragover.prevent="dragging = true"
           x-on:dragleave.prevent="dragging = false"
           x-on:drop.prevent="drop($event)">
        @include('livewire.admin.media.partials.icon', ['name' => 'upload'])
        <span class="ahm-dropzone-title">Drag and drop files here or click to browse</span>
        <span id="file-upload-help-{{ $this->getId() }}">
            Images, PDFs, documents, videos and audio. Maximum size is 20MB per file.
        </span>
    </label>

    <progress class="uk-progress ahm-progress" x-show="uploading" x-cloak :value="progress" max="100"></progress>

    {{-- When a transfer is refused, Livewire's own message is just "The files.0 failed to upload". --}}
    <p class="ahm-error" x-show="failed" x-cloak>
        The upload failed. The file may be larger than the server allows.
    </p>
    <div x-show="! failed">
        @error('files')
            <p class="ahm-error">{{ $message }}</p>
        @enderror
        @error('files.*')
            <p class="ahm-error">{{ $message }}</p>
        @enderror
    </div>
    @foreach ($uploadErrors as $error)
        <p class="ahm-error">{{ $error }}</p>
    @endforeach

    @if (! empty($files))
        <div class="ahm-queue">
            @foreach ($files as $index => $file)
                <div class="ahm-queue-item" wire:key="queue-{{ $file->getFilename() }}">
                    <div class="ahm-queue-thumb">
                        @if (isset($previews[$index]) && Str::startsWith($file->getMimeType(), 'image/'))
                            <img src="{{ $previews[$index] }}" alt="{{ $file->getClientOriginalName() }}">
                        @else
                            @include('livewire.admin.media.partials.icon', ['name' => $this->getFileType($file->getClientOriginalExtension())])
                        @endif
                    </div>

                    <div class="ahm-queue-fields">
                        <input type="text" wire:model="customFilenames.{{ $index }}" class="uk-input uk-form-small"
                               placeholder="Custom filename" aria-label="Custom filename for {{ $file->getClientOriginalName() }}">
                        @error('customFilenames.' . $index)
                            <span class="ahm-error">{{ $message }}</span>
                        @enderror

                        @if (Str::startsWith($file->getMimeType(), 'image/'))
                            <input type="text" wire:model="imgDescriptions.{{ $index }}" class="uk-input uk-form-small"
                                   placeholder="Image description" aria-label="Image description for {{ $file->getClientOriginalName() }}">
                            @error('imgDescriptions.' . $index)
                                <span class="ahm-error">{{ $message }}</span>
                            @enderror
                        @endif

                        <span class="ah-table-meta">
                            {{ $file->getClientOriginalName() }} · {{ $this->formatBytes($file->getSize()) }} · {{ ucfirst($this->getFileType($file->getClientOriginalExtension())) }}
                        </span>
                    </div>

                    <button type="button" class="ahm-icon-btn" wire:click="removeFile({{ $index }})"
                            title="Remove" aria-label="Remove {{ $file->getClientOriginalName() }}">
                        @include('livewire.admin.media.partials.icon', ['name' => 'close'])
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="uk-button uk-button-primary ahm-upload-submit" wire:click="save"
                wire:loading.attr="disabled" wire:target="save" :disabled="uploading">
            <span wire:loading.remove wire:target="save">Upload {{ count($files) === 1 ? 'file' : count($files) . ' files' }}</span>
            <span wire:loading wire:target="save">Uploading…</span>
        </button>
    @endif
</div>
