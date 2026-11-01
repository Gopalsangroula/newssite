<div uk-grid class="uk-grid-medium uk-margin-remove-top uk-center">
    <div class="uk-width-1-1@m">
        <div class="cat-title uk-flex uk-flex-between uk-flex-middle uk-margin-small-bottom">
            <h2 class="uk-width-expand uk-heading-line uk-margin-remove">
                <span>
                    <a href="/entertainment" class="ah-title-cat">मनोरञ्जन</a>
                </span>
            </h2>
            <a href="{{ url('/business') }}" class="uk-margin-small-left ah-title-more">थप समाचार</a>
        </div>
    </div>
</div>

<div uk-grid class="uk-grid-match">
    <div class="uk-width-2-3@m">
        <div class="uk-child-width-1-2@s uk-text-center" uk-grid>
            @foreach($entPosts->take(2) as $post)
                <div>
                    <div class="uk-animation-toggle np-samachar-list uk-height-1-1" tabindex="0">
                        <div class="uk-cover-container uk-height-1-1">
                            <img
                                src="{{ $post->cover_image ? asset('storage/'.$post->cover_image) : asset('storage/default.jpg') }}"
                                class="uk-height-1-1"
                                style="object-fit: cover;"
                                alt="{{ $post->title }}"
                            >
                            <a href="{{ route('frontend.details.show', $post->slug) }}">
                                <div class="uk-overlay-our uk-position-cover"></div>
                            </a>
                            <div class="uk-overlay uk-position-bottom uk-light">
                                <h2>
                                    <a href="{{ route('frontend.details.show', $post->slug) }}" class="ah">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Right column --}}
    <div class="uk-width-1-3@s">
        @foreach($arthaPosts->slice(2, 4) as $post)
            <div class="margin-s tranding-item">
                <div class="uk-card uk-card-small uk-grid-collapse" uk-grid>
                    <div class="uk-width-2-5 uk-card-media-left">
                        <a href="{{ route('frontend.details.show', $post->slug) }}">
                            <img
                                src="{{ $post->cover_image ? asset('storage/'.$post->cover_image) : asset('storage/default.jpg') }}"
                                alt="{{ $post->title }}"
                            >
                        </a>
                    </div>
                    <div class="uk-width-3-5">
                        <div class="margin-s-l uk-flex-last@s uk-margin-small-left">
                            <h4 class="uk-margin-remove-top">
                                <a href="{{ route('frontend.details.show', $post->slug) }}" class="ah">
                                    {{ $post->title }}
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
