<div class="single-page-container uk-container uk-container-large">
    <!-- Page Header -->
    <header class="uk-margin-medium-top">
        <div uk-grid class=" uk-margin-medium-bottom uk-grid-collapse">
            <div class="uk-width-small@m"></div>
            <div class="uk-width-expand@m">
                <div class="page-meta-single">
                    <h1 class="uk-heading-small uk-margin-small ah-single-heading uk-text-center">{{ $page->title }}</h1>
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

     <div class="reader-effect uk-flex-center">
                                    <div class="sharethis-inline-share-buttons" style="z-index: 999 !important;"></div>
                                </div>

</div>

@push('scripts')
<script>
    // Ensure UIkit components are initialized
    document.addEventListener('livewire:init', function() {
        // UIkit will automatically initialize components
    });
</script>
@endpush