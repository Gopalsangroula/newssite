{{-- Shared by posts/create and posts/edit. Expects $isEdit. --}}
@php
    $primaryLabel = match ($status) {
        'scheduled' => 'Schedule',
        'draft' => 'Save',
        default => $isEdit ? 'Update' : 'Publish',
    };
    $draftLabel = $isEdit && $temporaryPost->status === 'published' ? 'Switch to Draft' : 'Save Draft';
@endphp

<div class="ah-editor-fields">
    <div class="ah-editor-topbar">
        <a href="{{ route('admin.posts.index') }}" class="ah-editor-back">
            <span uk-icon="icon: arrow-left" wire:ignore></span>
            <span>All Posts</span>
        </a>

        <div class="ah-editor-actions">
            <a href="#seo-panel" class="uk-button uk-button-default">SEO</a>
            @if ($isEdit && $temporaryPost->status === 'published')
                <a href="{{ route('frontend.details.show', $temporaryPost->slug) }}" target="_blank" rel="noopener" class="uk-button uk-button-default">View post</a>
            @endif
            <button type="button" class="uk-button uk-button-default" wire:click="saveDraft" wire:loading.attr="disabled">{{ $draftLabel }}</button>
            <button type="button" class="uk-button uk-button-primary" wire:click="save" wire:loading.attr="disabled">{{ $primaryLabel }}</button>
        </div>
    </div>

    @include('partials.editor-alerts')

    <div class="ah-editor-layout">
        <div class="ah-editor-main">
            <div class="ah-block-editor" wire:ignore>
                <textarea id="title" wire:model="title" class="ah-editor-title" rows="1" placeholder="Add title" aria-label="Post title"></textarea>
                <textarea id="editor" wire:model="content" class="uk-textarea" rows="16" aria-label="Post content">{{ $content }}</textarea>
            </div>

            @include('partials.editor-seo', [
                'seoPath' => '/details/',
                'seoUsesSubTitle' => true,
                'seoUsesExcerpt' => true,
                'seoPublicUrl' => $isEdit && $temporaryPost->status === 'published' ? route('frontend.details.show', $temporaryPost->slug) : null,
            ])
        </div>

        <aside class="ah-editor-side">
            <details class="ah-panel" open wire:ignore.self>
                <summary>Status &amp; visibility</summary>
                <div class="ah-panel-body">
                    <label class="ah-field-label" for="status">Status</label>
                    <select wire:model.live="status" id="status" class="uk-select">
                        <option value="published">Publish immediately</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Schedule</option>
                    </select>

                    @if ($status === 'scheduled')
                        <label class="ah-field-label" for="published_at">Publish date (Nepal time)</label>
                        <input type="datetime-local" wire:model="published_at" id="published_at" class="uk-input">
                    @endif

                    <label class="ah-check"><input type="checkbox" class="uk-checkbox" wire:model="is_featured"> Featured</label>
                    <label class="ah-check"><input type="checkbox" class="uk-checkbox" wire:model="is_breaking"> Breaking news</label>

                    <label class="ah-field-label" for="kicker">Top story label</label>
                    <input type="text" wire:model="kicker" id="kicker" class="uk-input" maxlength="40" placeholder="e.g. विशेष">
                    <p class="ah-panel-hint">The red tag above the title when this post is Featured at the top of the homepage. Leave it empty for none.</p>

                    @if ($isEdit)
                        <button type="button" class="ah-panel-danger" wire:click="deletePost" wire:confirm="Move this post to the trash?" wire:loading.attr="disabled">Move to trash</button>
                    @elseif ($temporaryPost)
                        <button type="button" class="ah-panel-danger" wire:click="deleteDraft" wire:confirm="Delete this draft?" wire:loading.attr="disabled">Delete draft</button>
                    @endif
                </div>
            </details>

            @include('partials.editor-cover')

            <details class="ah-panel" open wire:ignore.self>
                <summary>Featured video</summary>
                <div class="ah-panel-body">
                    <input type="url" wire:model.blur="video_url" id="video_url" @class(['uk-input', 'uk-form-danger' => $errors->has('video_url')]) placeholder="YouTube or Facebook link" aria-label="Featured video link">
                    <p class="ah-panel-hint">Plays in place of the featured image at the top of the homepage, in Breaking news and on the post page. The featured image stays as its cover picture.</p>
                </div>
            </details>

            <details class="ah-panel" open wire:ignore.self>
                <summary>Category</summary>
                <div class="ah-panel-body" wire:ignore>
                    <select id="category_id" data-choice="category_id" aria-label="Category">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($category->id == $category_id)>{{ $category->name }}</option>
                            @foreach ($category->children as $child)
                                <option value="{{ $child->id }}" @selected($child->id == $category_id)>— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </details>

            <details class="ah-panel" open wire:ignore.self>
                <summary>Tags</summary>
                <div class="ah-panel-body" wire:ignore>
                    <select id="tags" data-choice="tags" multiple aria-label="Tags">
                        @foreach ($allTags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $tags))>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
            </details>

            <details class="ah-panel" open wire:ignore.self>
                <summary>Authors</summary>
                <div class="ah-panel-body" wire:ignore>
                    <select id="user_ids" data-choice="user_ids" multiple aria-label="Authors">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, $user_ids))>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </details>

            <details class="ah-panel" open wire:ignore.self>
                <summary>Excerpt</summary>
                <div class="ah-panel-body">
                    <textarea wire:model="excerpt" id="excerpt" @class(['uk-textarea', 'uk-form-danger' => $errors->has('excerpt')]) rows="4" maxlength="300" placeholder="A short summary of the story" aria-label="Excerpt"></textarea>
                    <p class="ah-panel-hint">Shown under the headline on the homepage and in story lists, and in search and share previews unless SEO has its own description. Leave it empty to use the start of the story. Up to 300 characters.</p>
                </div>
            </details>

            <details class="ah-panel" wire:ignore.self>
                <summary>Sub title</summary>
                <div class="ah-panel-body">
                    <input type="text" wire:model="sub_title" id="sub_title" class="uk-input" placeholder="Optional sub title" aria-label="Post sub title">
                </div>
            </details>

            <details class="ah-panel" wire:ignore.self>
                <summary>Permalink</summary>
                <div class="ah-panel-body">
                    <input type="text" wire:model="slug" id="slug" class="uk-input" placeholder="auto-generated-if-empty" aria-label="Post slug">
                </div>
            </details>
        </aside>
    </div>

    @include('partials.uploads')
    @include('partials.block-editor')
</div>
