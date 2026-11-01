<div class="lb-page lb-page--category">
    @include('livewire.frontend.partials.page-head', [
        'title' => $category->name,
        'count' => $posts->total() ? $posts->total() . ' सामग्री' : null,
    ])

    @include('livewire.frontend.partials.post-list', [
        'posts' => $posts,
        'emptyTitle' => 'यस वर्गमा अहिलेसम्म कुनै सामग्री प्रकाशित भएको छैन',
        'emptyText' => 'नयाँ सामग्री प्रकाशित भएपछि यहाँ देखिनेछ।',
    ])
</div>
