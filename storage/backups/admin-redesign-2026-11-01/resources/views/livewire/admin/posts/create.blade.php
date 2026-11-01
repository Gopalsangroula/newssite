<div>
    {{-- <h1 class="text-2xl font-bold uk-margin-bottom ah-admin-title uk-text-center">Create New Post</h1> --}}

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

    <form wire:submit.prevent="save" enctype="multipart/form-data" class="uk-form-stacked">
        <div class="uk-grid" uk-grid>
            {{-- Left Column --}}
            <div class="uk-width-expand@m  uk-margin-remove-left uk-padding-remove">
                {{-- Title --}}
                <div class="uk-margin">
                    <label class="uk-form-label" for="title">Post Title</label>
                    <input type="text" wire:model.defer="title" id="title" class="uk-input"
                        placeholder="Enter post title" aria-label="Post title">
                    @error('title') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>


                {{-- Content --}}
                <div class="uk-margin" wire:ignore>
                    <label class="uk-form-label" for="editor">Content</label>
                    <textarea id="editor" wire:model.debounce.500ms="content"
                        class="uk-textarea tinymce-editor">{{ $content }}</textarea>
                    @error('content') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Right Column --}}
            <div class="uk-width-1-5@m">
                {{-- Cover Image --}}
                <div class="uk-margin">
                    <label class="uk-form-label">Cover Image</label>
                    <div class="uk-margin-small-top">
                        <button type="button" class="uk-button uk-button-default uk-margin-small-bottom ah-select"
                            onclick="openImagePickerForCover()" aria-label="Select cover image from media library">
                            Select from Media Library
                        </button>
                          <div uk-form-custom>
                                <input type="file" wire:model="cover_image" accept="image/*" class="uk-input" id="cover_image"  aria-label="Upload cover image">
                                <button class="uk-button uk-button-default ah-select" type="button" tabindex="-1">Select form browser</button>
                            </div>
                        @error('cover_image') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                        @error('cover_image_url') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror

                        {{-- Preview --}}
                        @if ($cover_image || $cover_image_url)
                            <div class="uk-margin-top">
                                <img src="{{ str_replace('/storage/storage/', '/storage/', $cover_image ? $cover_image->temporaryUrl() : $cover_image_url) }}" 
                                    class="uk-width-small uk-border-rounded" 
                                    alt="Cover Image Preview" />
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Authors with Choices.js Fix --}}
                <div class="uk-margin" wire:ignore>
                    <label class="uk-form-label" for="user_ids">Authors</label>
                    <select id="user_ids" data-choice multiple>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @if(in_array($user->id, $user_ids)) selected @endif>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_ids.*') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                {{-- Category --}}
                <div class="uk-margin">
                    <label class="uk-form-label" for="category_id">Category</label>
                    <select class="uk-select" wire:model="category_id" id="category_id" aria-label="Select category">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @foreach($category->children as $child)
                                <option value="{{ $child->id }}">— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('category_id') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>
                {{-- Title --}}
                <div class="uk-margin">
                    <label class="uk-form-label" for="sub_title">Sub Title (Optional)</label>
                    <input type="text" wire:model.defer="sub_title" id="sub_title" class="uk-input"
                        placeholder="Enter post sub title" aria-label="Post sub title">
                    @error('sub_title') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>
                {{-- Tags --}}
                <div class="uk-margin" wire:ignore>
                    <label class="uk-form-label" for="tags">Tags</label>
                    <select id="tags" data-choice multiple>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag->id }}" @if(in_array($tag->id, $tags)) selected @endif>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tags.*') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>

                {{-- Status + Publish Date --}}
                <div class="uk-margin" x-data="{ status: @entangle('status') }">
                    <label class="uk-form-label" for="status">Status</label>
                    <select wire:model="status" id="status" class="uk-select" aria-label="Post status">
                        <option value="published">Publish Immediately</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Schedule</option>
                    </select>
                    @error('status') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror

                    <div class="uk-margin-small-top" x-show="status === 'scheduled'" x-cloak>
                        <label class="uk-form-label" for="published_at">Publish Date</label>
                        <input type="datetime-local" wire:model.lazy="published_at" id="published_at" class="uk-input"
                            aria-label="Publish date">
                        @error('published_at') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Checkboxes --}}
                <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                    <label><input type="checkbox" class="uk-checkbox" wire:model="is_featured" aria-label="Featured post"> Featured</label>
                    <label><input type="checkbox" class="uk-checkbox" wire:model="is_breaking" aria-label="Breaking news"> Breaking</label>
                </div>
                     {{-- Slug --}}
                <div class="uk-margin">
                    <label class="uk-form-label" for="slug">Slug (Optional)</label>
                    <input type="text" wire:model.defer="slug" id="slug" class="uk-input"
                        placeholder="Enter post slug" aria-label="Post slug">
                    @error('slug') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                </div>
                              
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="uk-width-1-1 uk-margin-top uk-flex-center uk-flex">
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled">Save Post</button>
            <button type="button" class="uk-button uk-button-secondary uk-margin-small-left" wire:click="saveDraft" wire:loading.attr="disabled">Save Draft</button>
            @if($temporaryPost)
                <button type="button" class="uk-button uk-button-danger uk-margin-small-left" wire:click="deleteDraft" wire:loading.attr="disabled">Delete Draft</button>
            @endif
            <a href="{{ route('admin.posts.index') }}" class="uk-button uk-button-default uk-margin-small-left">Cancel</a>
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