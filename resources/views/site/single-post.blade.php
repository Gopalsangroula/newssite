@php
    $shareUrl = route('frontend.details.show', $post->slug);
    $shareTitle = $post->title;

    // The stand-in author of a deleted account has no page to link to.
    $authorUrl = fn ($author) => $author->slug ? route('frontend.author.show', $author->slug) : '#';
@endphp

@if (Str::contains($post->content, 'class="fb-'))
    {{-- Only stories with an embedded Facebook post need Facebook's script. --}}
    @push('scripts')
        <div id="fb-root"></div>
        <script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v19.0"></script>
    @endpush
@endif

<div class="ah-single-section" data-read-id="{{ $post->id }}">
    <!-- Post Header -->
    <div class="uk-text-center" uk-grid>
        <div class="uk-width-1-1@m">
            <header class="uk-margin-medium-top">
                @if($post->is_breaking)
                <span class="uk-label uk-label-danger uk-margin-bottom uk-text-uppercase">Breaking News</span>
                @elseif($post->is_featured)
                <span class="uk-label uk-label-success uk-margin-bottom uk-text-uppercase">Featured</span>
                @endif

                <h1 class="uk-heading-small uk-margin-small ah-single-heading">{{ $post->title }}</h1>

                @if($post->sub_title)
                <h2 class="uk-text-lead uk-text-muted uk-margin-medium-bottom">{{ $post->sub_title }}</h2>
                @endif

                {{-- The post's own video takes the place of its picture, unless the story already plays it. --}}
                @if($post->video && ! Str::contains($post->content, $post->video['id']))
                    @include('site.partials.video-player', ['post' => $post, 'class' => 'ah-single-video', 'isFirst' => true])
                @elseif($post->cover_image)
                <div class="mb-8">
                    <img src="{{ \App\Support\Thumb::url($post->cover_image, 1024) }}" alt="{{ $post->title }}" title="{{ $post->img_desc }}" class="w-full h-auto rounded-lg shadow-md object-cover max-h-96" fetchpriority="high" decoding="async">
                </div>
                @endif
            </header>
        </div>
    </div>

    <div class="ah-single-content-section" uk-grid>
      
        <div class="uk-width-1-1@m">
            <div class="uk-text-center uk-grid-collapse" uk-grid>
                <div class="uk-width-1-1 uk-flex-wrap uk-flex-middle">
                    <div class="uk-flex uk-flex-column uk-text-meta uk-margin-medium ah-author">
                        @php
                        // Ensure authors is always a collection (fallback if needed)
                        $authors = $post->users ?? collect();
                        if ($authors->isEmpty()) {
                        $fallbackAuthor = $post->createdBy ?? $post->publishedBy ?? null;
                        $authors = $fallbackAuthor ? collect([$fallbackAuthor]) : collect();
                        }
                        @endphp

                        @if($authors->count() === 1)
                        @php $author = $authors->first(); @endphp
                        <div class="uk-flex uk-flex-middle uk-card uk-card-small uk-border-rounded">
                            <div class="ah-author-image uk-transition-toggle uk-margin-small-right">
                                <a href="{{ $authorUrl($author) }}" class="uk-link-reset"
                                    aria-label="View {{ $author->name }}'s profile">
                                    @if($author->user_image)
                                    <img src="{{ \App\Support\Thumb::url($author->user_image, 320) }}"
                                        alt="{{ $author->name }}'s profile image"
                                        class="uk-border-circle uk-transition-scale-up uk-transition-opaque" loading="lazy" decoding="async">
                                    @else
                                    <span
                                        class="uk-border-circle uk-background-muted uk-flex uk-flex-center uk-flex-middle uk-text-bold"
                                        style="width: 80px; height: 80px;">
                                        {{ Str::upper(Str::substr($author->name, 0, 1)) }}
                                    </span>
                                    @endif
                                </a>
                            </div>
                            <div class="uk-width-1-1 uk-flex uk-flex-wrap uk-flex-middle uk-margin-small-top">
                                <div class="pub-date uk-margin-medium-right">
                                    <div class="ah-author-name">
                                        <a href="{{ $authorUrl($author) }}"
                                            class="uk-link-heading uk-text-small uk-text-bolder"
                                            style="color: #1a1a1a; text-decoration: none;"
                                            onmouseover="this.style.textDecoration='underline'"
                                            onmouseout="this.style.textDecoration='none'">
                                            {{ $author->name }}
                                        </a>
                                    </div>
                                    <span class="ah-pub-date">
                                        प्रकाशित मिति: {{ \App\Support\NepaliDate::withTime($post->published_at ?? $post->created_at) }}
                                    </span>
                                </div>
                                <div class="reader-effect uk-flex uk-flex-center uk-flex-middle uk-flex-wrap post-actions">
                                    @include('site.partials.share-bar')
                                    @include('site.partials.post-tools')
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="uk-grid uk-child-width-auto uk-flex-middle uk-flex-wrap uk-grid-small" uk-grid
                            style="gap: 1rem;">
                            @foreach($authors as $author)
                            <div class="uk-flex uk-flex-column uk-flex-center uk-text-center uk-transition-toggle">
                                <div class="ah-author-image uk-margin-small-bottom">
                                    <a href="{{ $authorUrl($author) }}" class="uk-link-reset"
                                        aria-label="View {{ $author->name }}'s profile">
                                        @if($author->user_image)
                                        <img src="{{ \App\Support\Thumb::url($author->user_image, 320) }}"
                                            alt="{{ $author->name }}'s profile image"
                                            class="uk-border-circle uk-transition-scale-up uk-transition-opaque uk-box-shadow-small"
                                            width="80" height="80" loading="lazy" decoding="async">
                                        @else
                                        <span
                                            class="uk-border-circle uk-background-muted uk-flex uk-flex-center uk-flex-middle uk-text-bold"
                                            style="width: 80px; height: 80px; background: linear-gradient(135deg, #e0e0e0, #f5f5f5); color: #333;">
                                            {{ Str::upper(Str::substr($author->name, 0, 1)) }}
                                        </span>
                                        @endif
                                    </a>
                                </div>
                                <div class="ah-author-name">
                                    <a href="{{ $authorUrl($author) }}"
                                        class="uk-link-heading uk-text-small uk-text-bolder"
                                        style="color: #1a1a1a; text-decoration: none;"
                                        onmouseover="this.style.textDecoration='underline'"
                                        onmouseout="this.style.textDecoration='none'">
                                        {{ $author->name }}
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div
                            class="uk-width-1-1 uk-flex uk-flex-wrap uk-flex-middle uk-flex-center uk-margin-small-top">
                            <div class="pub-date uk-margin-medium-right">
                                <span class="ah-pub-date">
                                    प्रकाशित मिति: {{ \App\Support\NepaliDate::withTime($post->published_at ?? $post->created_at) }}
                                </span>
                            </div>
                            <div class="reader-effect uk-flex uk-flex-center uk-flex-middle uk-flex-wrap post-actions">
                                @include('site.partials.share-bar')
                                @include('site.partials.post-tools')
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="uk-divider-icon">

            <!-- Post Content -->
            <article class="uk-article uk-margin-large-bottom ah-post-entry">
                {!! \App\Support\Thumb::inContent($post->content) !!}

                <!-- Tags -->
                @if($post->tags->count())
                <div class="uk-margin-small-top">
                    <div class="uk-flex uk-flex-wrap uk-margin-small">
                        @foreach($post->tags as $tag)
                        <a href="{{ route('frontend.tag.show', $tag->slug) }}"
                        class="uk-badge uk-margin-small-right uk-margin-small-bottom uk-text-normal uk-background-muted uk-text-emphasis uk-padding-small uk-transition-toggle">
                            {{ $tag->name }}
                            <span class="uk-transition-slide-right-small uk-text-primary uk-margin-small-left">→</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </article>


            <div class="share-buttons uk-margin">
                {{-- Facebook --}}
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank"
                    rel="noopener noreferrer nofollow" class="uk-icon-button" uk-icon="facebook" aria-label="Facebook"></a>

                {{-- Twitter (X) --}}
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($post->title) }}"
                    target="_blank" rel="noopener noreferrer nofollow" class="uk-icon-button" uk-icon="twitter" aria-label="X"></a>

                {{-- LinkedIn --}}
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}"
                    target="_blank" rel="noopener noreferrer nofollow" class="uk-icon-button" uk-icon="linkedin" aria-label="LinkedIn"></a>

                {{-- WhatsApp (mobile/desktop) --}}
                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . $shareUrl) }}"
                    target="_blank" rel="noopener noreferrer nofollow" class="uk-icon-button" uk-icon="whatsapp" aria-label="WhatsApp"></a>
            </div>

            @include('site.post-comments')
        </div>
    </div>
</div>
