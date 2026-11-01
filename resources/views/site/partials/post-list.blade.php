{{--
    Post cards with pagination, or the empty state. Shared by the category, tag and author pages.
    Takes $posts (a paginator), $emptyTitle and $emptyText; $headingTag and $hideAuthor pass through to the cards.
--}}
<section class="lb-results">
    @if($posts->isNotEmpty())
        <div class="lb-grid">
            @foreach($posts as $post)
                @include('site.partials.post-card', [
                    'post' => $post,
                    'headingTag' => $headingTag ?? 'h2',
                    'hideAuthor' => $hideAuthor ?? false,
                ])
            @endforeach
        </div>

        {{ $posts->onEachSide(1)->links('site.partials.pagination') }}
    @else
        @include('site.partials.empty-state', ['title' => $emptyTitle, 'text' => $emptyText])
    @endif
</section>
