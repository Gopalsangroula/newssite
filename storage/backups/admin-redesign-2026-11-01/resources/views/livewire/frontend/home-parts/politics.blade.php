<div class="uk-margin-medium-top">
    <div uk-grid="uk-grid" class="uk-grid-medium">
        <div class="uk-width-3-4@m">
            <div class="cat-title uk-flex uk-flex-between uk-flex-middle uk-margin-small-bottom">
                <h2 class="uk-width-expand uk-heading-line uk-margin-remove">
                    <span>
                        <a href="{{ url('/politices') }}" class="ah-title-cat">राजनीति</a>
                    </span>
                </h2>
                <a href="{{ url('/politices') }}" class="uk-margin-small-left ah-title-more">थप समाचार</a>
            </div>

            @if($politicsPosts->isNotEmpty())
                <!-- Featured Post -->
                <div class="uk-card uk-card-hover uk-card-default uk-grid-collapse uk-flex-middle uk-child-width-1-2@s uk-margin-remove image-flashing"
                     style="background: #eee;" uk-grid>
                    <div class="uk-card-media-left uk-cover-container">
                        <a href="{{ route('frontend.details.show', $politicsPosts[0]->slug) }}">
                            <img src="{{ $politicsPosts[0]->cover_image ? asset('storage/' . $politicsPosts[0]->cover_image) : asset('storage/default.jpg') }}"
                                 class="uk-height-large ah-image-section" alt="{{ $politicsPosts[0]->title }}">
                        </a>
                    </div>
                    <div>
                        <div class="uk-card-body" style="padding: 15px;">
                            <h2>
                                <a href="{{ route('frontend.details.show', $politicsPosts[0]->slug) }}" class="ah">
                                    {{ $politicsPosts[0]->title }}
                                </a>
                            </h2>
                             <p class="rich-para">{{ \Illuminate\Support\Str::limit(strip_tags($politicsPosts[0]->content), 240) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Other Posts -->
                @if($politicsPosts->count() > 1)
                    <div class="uk-child-width-1-3@s uk-child-width-1-2 uk-grid-match uk-margin-medium-top uk-grid-medium"
                         uk-grid="uk-grid">
                        @foreach($politicsPosts->slice(1) as $post)
                            <div>
                                <div class="list-item margin-s">
                                    <div class="uk-card uk-card-small">
                                        <a href="{{ route('frontend.details.show', $post->slug) }}">
                                            <img src="{{ $post->cover_image ? asset('storage/' . $post->cover_image) : asset('storage/default.jpg') }}"
                                                 alt="{{ $post->title }}" class="uk-border-rounded news-item-img">
                                        </a>
                                        <h4 class="uk-margin-small-top">
                                            <a href="{{ route('frontend.details.show', $post->slug) }}" class="ah">
                                                {{ $post->title }}
                                            </a>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        <div class="uk-width-1-4@m">
            <div class="ads">Ads</div>
        </div>
    </div>
</div>