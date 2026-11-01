{{--
    Post cards with pagination, or the empty state. Shared by the category, tag and author pages.
    Takes $posts (a paginator), $emptyTitle and $emptyText; $headingTag and $hideAuthor pass through to the cards.
--}}
<section class="lb-results" wire:loading.class="lb-is-busy">
    @if($posts->isNotEmpty())
        <div class="lb-grid">
            @foreach($posts as $post)
                @include('livewire.frontend.partials.post-card', [
                    'post' => $post,
                    'headingTag' => $headingTag ?? 'h2',
                    'hideAuthor' => $hideAuthor ?? false,
                ])
            @endforeach
        </div>

        {{ $posts->onEachSide(1)->links('livewire.frontend.partials.pagination') }}
    @else
        @include('livewire.frontend.partials.empty-state', ['title' => $emptyTitle, 'text' => $emptyText])
    @endif
</section>
