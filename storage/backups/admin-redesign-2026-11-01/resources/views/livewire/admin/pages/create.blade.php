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

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="uk-form-stacked">
        <div class="uk-grid" uk-grid>

            {{-- Left: Title + Content --}}
            <div class="uk-width-expand@m uk-margin-remove-left uk-padding-remove">
                <div class="uk-margin">
                    <label class="uk-form-label" for="title">Page Title *</label>
                    <input type="text" wire:model.live="title" id="title" class="uk-input"
                           placeholder="Enter page title">
                    @error('title') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                <div class="uk-margin" wire:ignore>
                    <label class="uk-form-label" for="editor">Content *</label>
                    <textarea id="editor" wire:model.debounce.500ms="content"
                              class="uk-textarea tinymce-editor">{{ $content }}</textarea>
                    @error('content') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Right: Sidebar --}}
            <div class="uk-width-1-4@m">

                {{-- Cover Image --}}
                <div class="uk-margin">
                    <label class="uk-form-label">Cover Image</label>
                    <div class="uk-margin-small-top">
                        <button type="button" class="uk-button uk-button-default uk-margin-small-bottom ah-select"
                                onclick="openImagePickerForCover()">
                            Select from Media Library
                        </button>

                        <div uk-form-custom>
                            <input type="file" wire:model="cover_image" accept="image/*" class="uk-input" id="cover_image">
                            <button class="uk-button uk-button-default ah-select" type="button" tabindex="-1">
                                Select from Browser
                            </button>
                        </div>

                        @error('cover_image') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                        @error('cover_image_url') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror

                        @if ($cover_image || $cover_image_url)
                            <div class="uk-margin-top">
                                <img src="{{ $cover_image ? $cover_image->temporaryUrl() : $cover_image_url }}"
                                     class="uk-width-small uk-border-rounded" alt="Cover preview">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Slug --}}
                <div class="uk-margin">
                    <label class="uk-form-label" for="slug">Slug (Optional)</label>
                    <input type="text" wire:model.live="slug" id="slug" class="uk-input"
                           placeholder="auto-generated-if-empty">
                    @error('slug') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                {{-- Status + Publish Date --}}
                <div class="uk-margin" x-data="{ status: @entangle('status') }">
                    <label class="uk-form-label" for="status">Status *</label>
                    <select wire:model.live="status" id="status" class="uk-select">
                        <option value="published">Publish Immediately</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Schedule</option>
                    </select>
                    @error('status') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror

                    <div class="uk-margin-small-top" x-show="status === 'scheduled'" x-cloak>
                        <label class="uk-form-label" for="published_at">Publish Date *</label>
                        <input type="datetime-local" wire:model.lazy="published_at" id="published_at" class="uk-input">
                        @error('published_at') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="uk-width-1-1 uk-margin-top uk-flex uk-flex-center">
            <button type="submit"
                    class="uk-button uk-button-primary"
                    wire:loading.attr="disabled">
                {{ $temporaryPage ? 'Update Page' : 'Publish Page' }}
            </button>

            <button type="button"
                    class="uk-button uk-button-secondary uk-margin-small-left"
                    wire:click="saveDraft"
                    wire:loading.attr="disabled">
                Save Draft
            </button>

            @if($temporaryPage)
                <button type="button"
                        class="uk-button uk-button-danger uk-margin-small-left"
                        wire:click="deleteDraft"
                        onclick="return confirm('Delete this draft permanently?')"
                        wire:loading.attr="disabled">
                    Delete Draft
                </button>
            @endif

            <a href="{{ route('admin.pages.index') }}"
               class="uk-button uk-button-default uk-margin-small-left">
                Cancel
            </a>
        </div>
    </form>

    {{-- Image Picker Modal --}}
    @include('partials.uploads')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TinyMCE --}}
    @include('partials.editorjs')
    <script src="{{ asset('/js/tinymce/tinymce.min.js') }}"></script>

    {{-- Choices.js --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Choices.js on elements with `data-choice`
            document.querySelectorAll('select[data-choice]').forEach((el) => {
                const choices = new Choices(el, {
                    removeItemButton: true,
                    searchEnabled: true,
                    shouldSort: false,
                });

                // Sync with Livewire when selection changes
                el.addEventListener('change', function () {
                    const selectedValues = Array.from(el.selectedOptions).map(opt => opt.value);
                    if (el.id === 'user_ids') {
                        @this.set('user_ids', selectedValues);
                    } else if (el.id === 'tags') {
                        @this.set('tags', selectedValues);
                    }
                });
            });
        });
    </script>
</div>