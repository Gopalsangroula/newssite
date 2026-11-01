<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Edit Comment</h1>
            <p class="ah-page-sub">
                Received {{ $comment->created_at?->format('M j, Y') }}
                @if ($comment->post && ! $comment->post->trashed())
                    on <a href="{{ route('admin.posts.edit', $comment->post->id) }}">{{ $comment->post->title }}</a>
                @elseif ($comment->post)
                    on {{ $comment->post->title }} (trashed)
                @endif
            </p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.comments.index') }}" class="uk-button uk-button-default">All Comments</a>
        </div>
    </div>

    <form wire:submit.prevent="update" class="ah-card ah-form-card ah-form-card--narrow" novalidate>
        <div class="ah-card-head">
            <h2 class="ah-card-title">Comment Details</h2>
        </div>

        <div class="ah-card-body">
            <div class="ah-form-grid">
                <div class="ah-field">
                    <label for="author_name" class="uk-form-label">Author Name <span class="ah-required">*</span></label>
                    <input id="author_name" type="text" wire:model="author_name" class="uk-input {{ $errors->has('author_name') ? 'uk-form-danger' : '' }}" placeholder="Enter author name">
                    @error('author_name') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="author_email" class="uk-form-label">Author Email <span class="ah-required">*</span></label>
                    <input id="author_email" type="email" wire:model="author_email" class="uk-input {{ $errors->has('author_email') ? 'uk-form-danger' : '' }}" placeholder="Enter author email">
                    @error('author_email') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field ah-field--full">
                    <label for="body" class="uk-form-label">Comment <span class="ah-required">*</span></label>
                    <textarea id="body" wire:model="body" rows="6" class="uk-textarea {{ $errors->has('body') ? 'uk-form-danger' : '' }}" placeholder="Enter comment"></textarea>
                    @error('body') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="ah-card-foot ah-form-actions">
            <a href="{{ route('admin.comments.index') }}" class="uk-button uk-button-default">Cancel</a>
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="update">Update Comment</button>
        </div>
    </form>
</div>
