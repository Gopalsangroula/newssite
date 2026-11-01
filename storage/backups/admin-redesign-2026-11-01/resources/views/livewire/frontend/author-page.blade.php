<div class="uk-container uk-padding">
    <!-- Breadcrumb -->

    <!-- Author Header -->
    <header class="uk-margin-large-bottom uk-text-center">
        <div class="uk-margin-bottom">
            @if($author->user_image)
                <img src="{{ Storage::url($author->user_image) }}" 
                     alt="{{ $author->name }}'s profile image" 
                     class="uk-border-circle uk-box-shadow-medium" 
                     width="120" height="120" uk-img>
            @else
                <span class="uk-border-circle uk-background-muted uk-flex uk-flex-center uk-flex-middle uk-box-shadow-medium uk-text-bold uk-text-large" 
                      style="width: 120px; height: 120px;">
                    {{ Str::upper(Str::substr($author->name, 0, 1)) }}
                </span>
            @endif
        </div>
        <h1 class="uk-heading-medium uk-margin-small">{{ $author->name }}</h1>
        @if($author->description)
            <p class="uk-text-lead uk-text-muted uk-margin-small uk-width-2-3@m uk-margin-auto">{{ $author->description }}</p>
        @endif
        @if($author->roles->count())
            <div class="uk-flex uk-flex-center uk-flex-wrap uk-margin-small">
                @foreach($author->roles as $role)
                    <span class="uk-badge uk-margin-small-right uk-margin-small-bottom uk-text-normal uk-background-primary uk-text-light">
                        {{ $role->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </header>

    <!-- Author's Posts -->
    @if($posts->count())
        <div class="uk-margin-large">
            <h2 class="uk-heading-line uk-margin-bottom"><span>Posts by {{ $author->name }}</span></h2>
            <div class="uk-grid-medium uk-child-width-1-1@s uk-child-width-1-3@m" uk-grid>
                @foreach($posts as $post)
                    <div>
                        <div class="uk-card uk-card-default uk-card-hover uk-border-rounded uk-overflow-hidden">
                            @if($post->cover_image)
                                <div class="uk-card-media-top">
                                    <img src="{{ Storage::url($post->cover_image) }}" 
                                         alt="{{ $post->title }}"
                                         class="uk-object-cover uk-height-medium"
                                         uk-img>
                                </div>
                            @endif
                            <div class="uk-card-body">
                                <h3 class="uk-card-title">
                                    <a href="{{ route('frontend.details.show', $post->slug) }}"
                                       class="uk-link-heading">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="uk-text-meta">{{ $post->published_at->format('F j, Y') }}</p>
                                <p class="uk-text-muted">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Pagination -->
            <div class="uk-margin-top">
                {{ $posts->links() }}
            </div>
        </div>
    @else
        <p class="uk-text-muted uk-text-center">No posts found for this author.</p>
    @endif
</div>