<div uk-grid class="uk-grid-medium uk-margin-remove-top uk-center">
    <div class="uk-width-1-1@m">
        <div class="cat-title uk-flex uk-flex-between uk-flex-middle uk-margin-small-bottom">
            <h2 class="uk-width-expand uk-heading-line uk-margin-remove">
                <span>
                    <a href="/business" class="ah-title-cat">अर्थ समाचार</a>
                </span>
            </h2>
            <a href="{{ url('/business') }}" class="uk-margin-small-left ah-title-more">थप समाचार</a>
        </div>
    </div>
</div>

@if($arthaPosts->isNotEmpty())
    {{-- First row: 2 large posts --}}
    <div class="uk-child-width-1-2@m uk-margin-remove-top" uk-grid>
        @foreach($arthaPosts->take(2) as $post)
            <div class="uk-animation-toggle np-samachar-list" tabindex="0">
                <div class="uk-cover-container">
                    <img src="{{ $post->cover_image ? asset('storage/'.$post->cover_image) : asset('storage/default.jpg') }}" class="uk-height-large uk-width-1-1"
                        style="object-fit: cover;">
                    <a href="{{ route('frontend.details.show', $post->slug) }}">
                        <div class="uk-overlay-our uk-position-cover"></div>
                    </a>
                    <div class="uk-overlay uk-position-bottom uk-light ah-global-main">
                        <h2><a href="{{ route('frontend.details.show', $post->slug) }}" class="ah">{{ $post->title }}</a></h2>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Second row: 4 small posts --}}
    <div class="uk-margin-medium-top uk-child-width-1-4@m" uk-grid>
        @foreach($arthaPosts->slice(2, 4) as $post)
            <div>
                <div class="uk-card uk-margin np-samachar-item">
                    <div class="uk-card-media-top img-wraper">
                        <a href="{{ route('frontend.details.show', $post->slug) }}">
                            <img src="{{ $post->cover_image ? asset('storage/'.$post->cover_image) : asset('storage/default.jpg') }}"
                                class="news-item-img">
                        </a>
                    </div>
                    <div>
                        <div class="uk-margin-top">
                            <h4>
                                <a href="{{ route('frontend.details.show', $post->slug) }}" class="ah">{{ $post->title }}</a>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif