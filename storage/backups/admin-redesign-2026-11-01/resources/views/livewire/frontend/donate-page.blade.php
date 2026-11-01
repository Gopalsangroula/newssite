<div class="single-page-container uk-container uk-container-large">
    <!-- Page Header -->
    <header class="uk-margin-medium-top">
        <div uk-grid class=" uk-margin-medium-bottom uk-grid-collapse">
            <div class="uk-width-small@m"></div>
            <div class="uk-width-expand@m">
                <div class="page-meta-single">
                    <h1 class="uk-heading-large uk-margin-small-bottom">{{ $page->title }} gopal</h1>
                </div>
            </div>
            <div class="uk-width-small@m"></div>
        </div>
        @if($page->cover_image)
            <div class="uk-margin-bottom">
                <img  src="{{ $page->cover_image_url }}"  alt="{{ $page->title }}" class="uk-width-1-1 uk-border-rounded"/>
            </div>
        @endif

    </header>

    <!-- Page Content -->
    <article class="uk-margin-large-bottom">
        <div class="uk-article-content uk-text-large">
            {!! $page->content !!}
        </div>
    </article>

    <!-- Comments Section (Optional) -->
    @if($showComments)
        <section class="uk-margin-large-bottom uk-padding uk-background-muted uk-border-rounded">
            <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
                <h3 class="uk-h3 uk-margin-remove">Comments</h3> 
                <button 
                    wire:click="toggleComments" 
                    class="uk-button uk-button-small uk-button-default"
                    aria-label="Hide comments"
                >
                    <span uk-icon="close" class="uk-margin-small-right"></span>
                    Hide Comments
                </button>
            </div>

            <!-- Add Comment Form -->
            @auth
                <div class="uk-margin-bottom">
                    <form wire:submit.prevent="addComment">
                        <div class="uk-margin">
                            <label for="newComment" class="uk-form-label">Add your comment:</label>
                            <textarea 
                                wire:model="newComment" 
                                id="newComment"
                                class="uk-textarea"
                                placeholder="Share your thoughts..."
                                rows="4"
                            ></textarea>
                            @error('newComment')
                                <div class="uk-text-danger uk-text-small">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="uk-button uk-button-primary">
                            page Comment
                        </button>
                    </form>
                </div>
            @else
                <div class="uk-text-center uk-padding">
                    <p class="uk-text-muted">
                        <a href="{{ route('login') }}" class="uk-link-text">Login</a> 
                        to leave a comment.
                    </p>
                </div>
            @endauth

            <!-- Comments List -->
            @if(method_exists($page, 'comments') && $page->comments->count() > 0)
                <div class="comments-list">
                    @foreach($page->comments as $comment)
                        <div class="uk-card uk-card-default uk-card-body uk-margin-small-bottom" wire:key="comment-{{ $comment->id }}">
                            <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
                                <span class="uk-text-bold">{{ $comment->user->name ?? 'Unknown' }}</span>
                                <span class="uk-text-meta">
                                    {{ $comment->created_at->format('M j, Y g:i A') }}
                                </span>
                            </div>
                            <div class="uk-text-break">
                                {{ $comment->content }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="uk-text-center uk-padding">
                    <p class="uk-text-muted">No comments yet. Be the first to comment!</p>
                </div>
            @endif
        </section>
    @else
        <div class="uk-text-center uk-margin-bottom">
            <button 
                wire:click="toggleComments" 
                class="uk-button uk-button-default"
                aria-label="Show comments"
            >
                <span uk-icon="comment" class="uk-margin-small-right"></span>
                Show Comments
            </button>
        </div>
    @endif

</div>

@push('scripts')
<script>
    // Ensure UIkit components are initialized
    document.addEventListener('livewire:init', function() {
        // UIkit will automatically initialize components
    });
</script>
@endpush