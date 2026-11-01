<div>

    {{-- Flash Messages --}}
    @if (session('message'))
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="update" enctype="multipart/form-data" class="uk-form-stacked">
        <div class="uk-grid" uk-grid>

            {{-- Left Column --}}
            <div class="uk-width-expand@m">

                {{-- Title --}}
                <div class="uk-margin">
                    <label class="uk-form-label">Page Title *</label>
                    <input type="text" wire:model.live="title" class="uk-input" placeholder="Enter page title">
                    @error('title') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                {{-- Content Editor --}}
                <div class="uk-margin" wire:ignore>
                    <label class="uk-form-label">Content *</label>
                    <textarea id="editor"
                              wire:model.debounce.500ms="content"
                              class="uk-textarea tinymce-editor"></textarea>
                    @error('content') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="uk-width-1-4@m">

                {{-- Cover Image --}}
                <div class="uk-margin">
                    <label class="uk-form-label">Cover Image</label>

                    <button type="button"
                            class="uk-button uk-button-default uk-margin-small-bottom"
                            onclick="openImagePickerForCover()">
                        Select from Media Library
                    </button>

                    <div uk-form-custom>
                        <input type="file" wire:model="cover_image" accept="image/*">
                        <button class="uk-button uk-button-default" type="button" tabindex="-1">
                            Upload from Device
                        </button>
                    </div>

                    @error('cover_image') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                    @error('cover_image_url') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror

                    {{-- Preview --}}
                    @if ($cover_image)
                        <div class="uk-margin-top">
                            <img src="{{ $cover_image->temporaryUrl() }}" class="uk-width-small uk-border-rounded">
                        </div>
                    @elseif ($cover_image_url)
                        <div class="uk-margin-top">
                            <img src="{{ $cover_image_url }}" class="uk-width-small uk-border-rounded">
                        </div>
                    @elseif ($page->cover_image)
                        <div class="uk-margin-top">
                            <img src="{{ Storage::url($page->cover_image) }}" class="uk-width-small uk-border-rounded">
                            <div class="uk-text-meta uk-text-small">Current cover image</div>
                        </div>
                    @endif
                </div>

                {{-- Slug --}}
                <div class="uk-margin">
                    <label class="uk-form-label">Slug *</label>
                    <input type="text" wire:model.live="slug" class="uk-input" placeholder="auto-generated-if-empty">
                    @error('slug') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                {{-- Status --}}
                <div class="uk-margin" x-data="{ status: @entangle('status') }">
                    <label class="uk-form-label">Status *</label>
                    <select wire:model.live="status" class="uk-select">
                        <option value="published">Publish Immediately</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Schedule</option>
                    </select>

                    @error('status') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror

                    {{-- Scheduled Publish --}}
                    <div class="uk-margin-small-top" x-show="status === 'scheduled'" x-cloak>
                        <label class="uk-form-label">Publish Date *</label>
                        <input type="datetime-local" wire:model.lazy="published_at" class="uk-input">
                        @error('published_at') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Page Info --}}
                <div class="uk-margin uk-padding-small uk-background-muted uk-border-rounded">
                    <h4 class="uk-text-small uk-text-bold">Page Information</h4>

                    <div class="uk-text-small">
                        <div class="uk-grid uk-grid-small" uk-grid>
                            <div class="uk-width-1-3 uk-text-muted">Created:</div>
                            <div class="uk-width-2-3">{{ $page->created_at->format('M j, Y g:i A') }}</div>

                            <div class="uk-width-1-3 uk-text-muted">Updated:</div>
                            <div class="uk-width-2-3">{{ $page->updated_at->format('M j, Y g:i A') }}</div>

                            <div class="uk-width-1-3 uk-text-muted">Author:</div>
                            <div class="uk-width-2-3">{{ $page->creator->name ?? 'Unknown' }}</div>

                            @if($page->published_at)
                                <div class="uk-width-1-3 uk-text-muted">Published:</div>
                                <div class="uk-width-2-3">{{ $page->published_at->format('M j, Y g:i A') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="uk-margin-top uk-flex uk-flex-center">
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled">
                Update Page
            </button>

            <button type="button" class="uk-button uk-button-secondary uk-margin-left"
                    wire:click="saveDraft" wire:loading.attr="disabled">
                Save as Draft
            </button>

            <button type="button" class="uk-button uk-button-danger uk-margin-left"
                    wire:click="deletePage"
                    onclick="return confirm('Are you sure?');"
                    wire:loading.attr="disabled">
                Delete Page
            </button>

            <a href="{{ route('admin.pages.index') }}"
               class="uk-button uk-button-default uk-margin-left">
                Cancel
            </a>
        </div>
    </form>

    @include('partials.uploads')
    @include('partials.editorjs')

    <script src="{{ asset('/js/tinymce/tinymce.min.js') }}"></script>

</div>
