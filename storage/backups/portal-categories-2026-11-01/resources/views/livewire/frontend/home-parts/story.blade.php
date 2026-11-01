{{--
    One story in a list. Takes $post, and optionally $showCategory for
    a mixed list, where the reader cannot tell the category from the section.
--}}
@php
    $storyUrl = route('frontend.details.show', $post->slug);
    $storyDate = $post->published_at ?? $post->created_at;
@endphp

<article class="hp-story">
    @if ($post->cover_image)
        <a class="hp-story__media" href="{{ $storyUrl }}" tabindex="-1" aria-hidden="true">
            <img src="{{ Storage::url($post->cover_image) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
        </a>
    @endif

    <div class="hp-story__body">
        @if (! empty($showCategory) && $post->category)
            <a class="hp-kicker" href="{{ route('frontend.category.show', $post->category->slug) }}">{{ $post->category->name }}</a>
        @endif

        <h3 class="hp-story__title"><a href="{{ $storyUrl }}">{{ $post->title }}</a></h3>

        @if ($storyDate)
            <p class="hp-meta"><time datetime="{{ $storyDate->toDateString() }}">{{ \App\Support\NepaliDate::ago($storyDate) }}</time></p>
        @endif
    </div>
</article>
