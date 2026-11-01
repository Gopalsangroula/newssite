<div class="single-page-container uk-container uk-container-large">
    <!-- Page Header -->
    <header class="uk-margin-medium-top">
        <div uk-grid class="uk-margin-medium-bottom uk-grid-collapse">
            <div class="uk-width-small@m"></div>
            <div class="uk-width-expand@m">
                <div class="page-meta-single">
                    <h1 class="uk-heading-small uk-margin-small ah-single-heading">{{ $page->title }}</h1>
                </div>
            </div>
            <div class="uk-width-small@m"></div>
        </div>
        @if($page->cover_image)
            <div class="uk-margin-bottom">
                <img src="{{ \App\Support\Thumb::url($page->cover_image, 1024) }}" alt="{{ $page->title }}"
                    class="uk-width-1-1 uk-border-rounded" />
            </div>
        @endif
    </header>

    <!-- Page Content & PDF Viewer -->
    <article class="uk-margin-large-bottom">
        <div class="uk-article-content uk-text-large">
            {{-- {!! $page->content !!} --}}
            <div style="width:100%; height:800px;">
                <iframe src="{{ asset('img/onlinelibangebook.pdf') }}" width="100%" height="100%"  style="border:none;" loading="lazy" title="PDF Document">
                </iframe>
            </div>

        </div>
    </article>

</div>
