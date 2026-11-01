<div class="lb-page lb-page--author">
    @include('site.partials.breadcrumb', ['crumbs' => [
        ['name' => 'गृहपृष्ठ', 'url' => $homeUrl],
        ['name' => $author->name, 'url' => null],
    ]])

    <header class="lb-profile lb-profile--author">
        @include('site.partials.avatar', [
            'name' => $author->name,
            'image' => $author->user_image,
            'size' => 'lg',
            'alt' => $author->name,
        ])

        <div class="lb-profile__main">
            <h1 class="lb-profile__name">{{ $author->name }}</h1>
            @if($author->description)
                <p class="lb-profile__text">{{ $author->description }}</p>
            @endif
            @if($posts->total())
                <ul class="lb-chips">
                    <li class="lb-chip lb-chip--green">{{ $posts->total() }} सामग्री</li>
                </ul>
            @endif
        </div>
    </header>

    @include('site.partials.post-list', [
        'posts' => $posts,
        'hideAuthor' => true,
        'emptyTitle' => 'यस लेखकका कुनै सामग्री प्रकाशित भएका छैनन्',
        'emptyText' => 'नयाँ सामग्री प्रकाशित भएपछि यहाँ देखिनेछ।',
    ])
</div>
