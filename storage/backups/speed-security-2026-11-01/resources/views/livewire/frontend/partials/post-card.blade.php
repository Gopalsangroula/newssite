{{--
    One post in a listing. Takes $post, and optionally $headingTag (default h2)
    and $hideAuthor for the author page, where the name would repeat on every card.
--}}
@php
    $headingTag = $headingTag ?? 'h2';
    $cardTitle = trim($post->title);

    // plainText() drops tags and Gutenberg block comments and decodes entities such as &nbsp;.
    $excerpt = trim(preg_replace('~https?://\S+~u', '', \App\Support\Seo::plainText($post->sub_title ?: $post->content)));

    // Many posts open by repeating the title, some have nothing else.
    if (Str::startsWith($excerpt, $plainTitle = \App\Support\Seo::plainText($cardTitle))) {
        $excerpt = preg_replace('/^[\s,।–—-]+/u', '', Str::after($excerpt, $plainTitle));
    }

    $cardDate = $post->published_at ?? $post->created_at;
    $cardAuthor = empty($hideAuthor) && $post->author?->id ? $post->author->name : null;
@endphp

<article class="lb-card" wire:key="post-{{ $post->id }}">
    <div class="lb-card__media">
        @if($post->cover_image)
            <img class="lb-card__img" src="{{ Storage::url($post->cover_image) }}" alt="{{ $cardTitle }}"
                 loading="lazy" decoding="async" onerror="this.remove()">
        @endif
    </div>

    <div class="lb-card__body">
        <{{ $headingTag }} class="lb-card__title">
            <a href="{{ route('frontend.details.show', $post->slug) }}">{{ $cardTitle }}</a>
        </{{ $headingTag }}>

        @if($excerpt !== '')
            <p class="lb-card__excerpt">{{ Str::limit($excerpt, 170) }}</p>
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
