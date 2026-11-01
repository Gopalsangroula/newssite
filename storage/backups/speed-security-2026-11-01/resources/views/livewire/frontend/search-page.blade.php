<div class="lb-page lb-page--search">
    @include('livewire.frontend.partials.page-head', [
        'title' => 'खोज',
        'count' => $posts?->total() ? \App\Support\NepaliDate::digits($posts->total()).' सामग्री' : null,
    ])

    <div class="tool-search">
        <label class="site-sr" for="search-page-input">खोज्ने शब्द</label>
        <span class="tool-search__icon" uk-icon="search" wire:ignore></span>
        <input id="search-page-input" class="uk-input" type="search" wire:model.live.debounce.400ms="q" placeholder="समाचार वा सामग्री खोज्नुहोस्" autocomplete="off">
    </div>

    @if ($posts !== null)
        @include('livewire.frontend.partials.post-list', [
            'posts' => $posts,
            'emptyTitle' => '"'.$term.'" सँग मिल्ने सामग्री भेटिएन',
            'emptyText' => 'अर्को शब्दबाट खोज्नुहोस्।',
        ])
    @endif
</div>
