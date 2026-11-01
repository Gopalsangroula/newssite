<div class="lb-page lb-page--tag">
    @include('site.partials.page-head', [
        'title' => $tag->name,
        'count' => $posts->total() ? $posts->total() . ' सामग्री' : null,
    ])

    @include('site.partials.post-list', [
        'posts' => $posts,
        'emptyTitle' => 'यस ट्यागमा अहिलेसम्म कुनै सामग्री प्रकाशित भएको छैन',
        'emptyText' => 'नयाँ सामग्री प्रकाशित भएपछि यहाँ देखिनेछ।',
    ])
</div>
