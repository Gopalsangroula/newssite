<div>
    <div class="banner-home-img uk-cover-container" uk-height-viewport="offset-bottom: 12">
        <div class="uk-grid uk-margin-remove-top">
            
            <!-- TEXT SECTION (1/3) -->
            <div class="uk-width-1-3@m uk-flex-middle" uk-grid>
                <div class="uk-center uk-padding ah-bhusal">
                    <div class="uk-container">
                            <!-- Dynamic Title -->
                            <h1 class="Ah-about-title"><a href="/pages/about">{{ $pageabout?->title }}</a> </h1>

                            <!-- Dynamic Content -->
                            <p class="ah-limbu-para">
                                {!! Str::limit(strip_tags($pageabout?->content), 350) !!}
                            </p>
                    </div>

                    <p uk-margin class="uk-text-center">
                        <a class="uk-button uk-button-default ah-read-more" href="/pages/about"> पूरा पढ्नुहोस् </a>
                    </p>
                </div>
            </div>

            <!-- SLIDER SECTION (2/3) -->
            <div class="uk-width-2-3@m uk-visible@m">
                <div uk-slideshow="animation: push; autoplay: true">
                    <div class="uk-position-relative uk-visible-toggle" tabindex="-1">

                        <ul class="uk-slideshow-items" uk-height-viewport="offset-bottom: 12">
                            @if($pageabout?->cover_image_url)
                                <li>
                                    <img src="{{ $pageabout->cover_image_url }}" class="uk-width-1-1" uk-cover>
                                </li>
                            @endif

                        </ul>

                        <a class="uk-position-center-left uk-position-small uk-hidden-hover slider-icon"
                           href="#" uk-slidenav-previous uk-slideshow-item="previous"></a>

                        <a class="uk-position-center-right uk-position-small uk-hidden-hover slider-icon"
                           href="#" uk-slidenav-next uk-slideshow-item="next"></a>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <style>
        @media (max-width: 640px) {
        .Ah-about-title {
        font-size: 27px;
        }
    }
    </style>
</div>
