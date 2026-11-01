<div class="lb-page lb-page--search">
    @include('site.partials.page-head', [
        'title' => 'खोज',
        'count' => $posts?->total() ? \App\Support\NepaliDate::digits($posts->total()).' सामग्री' : null,
    ])

    <form class="tool-search" action="{{ route('frontend.search') }}" method="get" role="search">
        <label class="site-sr" for="search-page-input">खोज्ने शब्द</label>
        <span class="tool-search__icon" uk-icon="search"></span>
        <input id="search-page-input" class="uk-input" type="search" name="q" value="{{ $term }}" maxlength="100" placeholder="समाचार वा सामग्री खोज्नुहोस्" autocomplete="off">
    </form>

    @if ($posts !== null)
        @include('site.partials.post-list', [
            'posts' => $posts,
            'emptyTitle' => '"'.$term.'" सँग मिल्ने सामग्री भेटिएन',
            'emptyText' => 'अर्को शब्दबाट खोज्नुहोस्।',
        ])
    @endif
</div>
