<div uk-grid class="uk-grid-medium uk-margin-remove-top uk-center">
    <div class="uk-width-expand@m">
        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-small-bottom">
            <h2 class="uk-width-expand uk-heading-line uk-text-center">
                <span>
                    <a href="/politices" class="uk-heading-small uk-margin-small-top uk-text-bold">पछिल्लो अपडेट </a>
                </span>
            </h2>
        </div>

        @if($newsPosts->isNotEmpty())
            <div class="uk-child-width-1-3@m uk-grid-medium uk-grid-match uk-margin-small-top" uk-grid>
                @foreach($newsPosts as $index => $post)
                    <div>
                        <div class="uk-card uk-margin uk-box-shadow-xlarge uk-box-shadow-hover-medium">
                            <a href="{{ route('frontend.details.show', $post->slug) }}">
                            <div class="uk-padding">
                                <h2 class="ah-news-title uk-margin-remove-bottom">
                                     {{ $post->title }}
                                </h2>
                                <div class="uk-margin-top">
                                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}</p>
                                    <p uk-margin  class="uk-button uk-button-default">पूरा पढ्नुहोस्</p>
                                </div>
                            </div>
                              </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
