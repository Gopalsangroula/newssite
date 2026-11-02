{{--
    One post in a listing. Takes $post, and optionally $headingTag (default h2)
    and $hideAuthor for the author page, where the name would repeat on every card.
--}}
@php
    $headingTag = $headingTag ?? 'h2';
    $cardTitle = trim($post->title);
    $excerpt = $post->summary(170);
    $cardDate = $post->published_at ?? $post->created_at;
    $cardAuthor = empty($hideAuthor) && $post->author?->id ? $post->author->name : null;
@endphp

<article class="lb-card">
    <div class="lb-card__media">
        @if($post->cover_image)
            <img class="lb-card__img" src="{{ \App\Support\Thumb::url($post->cover_image, 640) }}" alt="{{ $cardTitle }}"
                 loading="lazy" decoding="async" onerror="this.remove()">
        @endif
    </div>

    <div class="lb-card__body">
        <{{ $headingTag }} class="lb-card__title">
            <a href="{{ route('frontend.details.show', $post->slug) }}">{{ $cardTitle }}</a>
        </{{ $headingTag }}>

        @if($excerpt !== '')
            <p class="lb-card__excerpt">{{ $excerpt }}</p>
        @endif

        <p class="lb-card__meta">
            @if($cardDate)
                <time datetime="{{ $cardDate->toDateString() }}">{{ \App\Support\NepaliDate::format($cardDate) }}</time>
            @endif
            @if($cardAuthor)
                <span>{{ $cardAuthor }}</span>
            @endif
        </p>
    </div>
</article>
