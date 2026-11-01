<div class="uk-container uk-section-small">
    <!-- Category Header -->
    <div class="uk-margin-medium-bottom uk-text-center uk-text-left@m">
        <h1 class="uk-heading-small uk-margin-remove-bottom">
            {{ $category->name }}
        </h1>
        <hr class="uk-divider-small">
    </div>

    <!-- Post Grid -->
    @if($posts->count())
        <div class="uk-child-width-1-1 uk-child-width-1-2@s uk-child-width-1-3@m uk-grid-match" uk-grid>
            @foreach($posts as $post)
                <div>
                    <div class="uk-card uk-card-default uk-card-hover uk-padding-small uk-flex uk-flex-column uk-border-rounded">
                       <a class="uk-link-reset" href="{{ route('frontend.details.show', $post->slug) }}">
                        <img src=" {{ $post->cover_image ? asset('storage/' . $post->cover_image) : asset('storage/default.jpg') }}" alt="">
                        <h2 class="uk-card-title uk-margin-small-top">{{ $post->title }}</h2>
                        <p class="uk-text-meta uk-text-emphasis uk-flex-1">
                            {{ Str::limit($post->excerpt, 120) }}
                        </p>
                        </a>
                        <div class="uk-article-meta uk-margin-small-bottom">
                            <span uk-icon="icon: user; ratio: 0.8"></span> {{ $post->author->name ?? 'Admin' }}
                            <span class="uk-margin-small-left uk-margin-small-right">&bull;</span>
                            <span uk-icon="icon: calendar; ratio: 0.8"></span> {{ $post->published_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="uk-placeholder uk-text-center uk-padding-large">
            <span uk-icon="icon: album; ratio: 2" class="uk-text-muted"></span>
            <p class="uk-text-lead uk-margin-small-top">No posts found</p>
            <p class="uk-text-meta">There are no published posts available in this category yet.</p>
        </div>
    @endif

    <!-- Pagination -->
    <div class="uk-margin-large-top uk-flex uk-flex-center">
        {{ $posts->links() }}
    </div>
</div>