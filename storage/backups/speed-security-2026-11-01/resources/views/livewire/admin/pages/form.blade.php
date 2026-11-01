{{-- Shared by pages/create and pages/edit. Expects $isEdit. --}}
@php
    $primaryLabel = match ($status) {
        'scheduled' => 'Schedule',
        'draft' => 'Save',
        default => $isEdit ? 'Update' : 'Publish',
    };
    $savedPage = $isEdit ? $page : $temporaryPage;
    $draftLabel = $savedPage?->status === 'published' ? 'Switch to Draft' : 'Save Draft';
@endphp

<div class="ah-editor-fields">
    <div class="ah-editor-topbar">
        <a href="{{ route('admin.pages.index') }}" class="ah-editor-back">
            <span uk-icon="icon: arrow-left" wire:ignore></span>
            <span>All Pages</span>
        </a>

        <div class="ah-editor-actions">
            <a href="#seo-panel" class="uk-button uk-button-default">SEO</a>
            @if ($savedPage?->status === 'published')
                <a href="{{ route('frontend.frontend.pages.show', $savedPage->slug) }}" target="_blank" rel="noopener" class="uk-button uk-button-default">View page</a>
            @endif
            <button type="button" class="uk-button uk-button-default" wire:click="saveDraft" wire:loading.attr="disabled">{{ $draftLabel }}</button>
            <button type="button" class="uk-button uk-button-primary" wire:click="{{ $isEdit ? 'update' : 'save' }}" wire:loading.attr="disabled">{{ $primaryLabel }}</button>
        </div>
    </div>

    @include('partials.editor-alerts')

    <div class="ah-editor-layout">
        <div class="ah-editor-main">
            <div class="ah-block-editor" wire:ignore>
                <textarea id="title" wire:model="title" class="ah-editor-title" rows="1" placeholder="Add title" aria-label="Page title"></textarea>
                <textarea id="editor" wire:model="content" class="uk-textarea" rows="16" aria-label="Page content">{{ $content }}</textarea>
            </div>

            @include('partials.editor-seo', [
                'seoPath' => '/pages/',
                'seoUsesSubTitle' => false,
                'seoPublicUrl' => $savedPage?->status === 'published' ? route('frontend.frontend.pages.show', $savedPage->slug) : null,
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

                    @if ($isEdit)
                        <button type="button" class="ah-panel-danger" wire:click="deletePage" wire:confirm="Move this page to the trash?" wire:loading.attr="disabled">Move to trash</button>
                    @elseif ($temporaryPage)
                        <button type="button" class="ah-panel-danger" wire:click="deleteDraft" wire:confirm="Delete this draft?" wire:loading.attr="disabled">Delete draft</button>
                    @endif
                </div>
            </details>

            @include('partials.editor-cover')

            <details class="ah-panel" open wire:ignore.self>
                <summary>Permalink</summary>
                <div class="ah-panel-body">
                    <input type="text" wire:model="slug" id="slug" class="uk-input" placeholder="auto-generated-if-empty" aria-label="Page slug">
                </div>
            </details>

            @if ($isEdit)
                <details class="ah-panel" wire:ignore.self>
                    <summary>Page information</summary>
                    <div class="ah-panel-body">
                        <dl class="ah-info-list">
                            <dt>Created</dt>
                            <dd>{{ $page->created_at->format('M j, Y g:i A') }}</dd>
                            <dt>Updated</dt>
                            <dd>{{ $page->updated_at->format('M j, Y g:i A') }}</dd>
                            <dt>Author</dt>
                            <dd>{{ $page->creator->name ?? 'Unknown' }}</dd>
                            @if ($page->published_at)
                                <dt>Published</dt>
                                <dd>{{ $page->published_at->format('M j, Y g:i A') }}</dd>
                            @endif
                        </dl>
                    </div>
                </details>
            @endif
        </aside>
    </div>

    @include('partials.uploads')
    @include('partials.block-editor')
</div>
