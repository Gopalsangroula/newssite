{{-- One story in a list. Takes $post, and optionally $isRow to put a small picture beside the title. --}}
@php
    $storyUrl = route('frontend.details.show', $post->slug);
    $storyDate = $post->published_at ?? $post->created_at;
@endphp

<article @class(['hp-story', 'hp-story--row' => ! empty($isRow)])>
    @if ($post->cover_image)
        <a class="hp-story__media" href="{{ $storyUrl }}" tabindex="-1" aria-hidden="true">
            <img src="{{ \App\Support\Thumb::url($post->cover_image, empty($isRow) ? 640 : 320) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
        </a>
    @endif

    <div class="hp-story__body">
        <h3 class="hp-story__title"><a href="{{ $storyUrl }}">{{ $post->title }}</a></h3>

        @if ($storyDate)
            <p class="hp-meta"><time datetime="{{ $storyDate->toDateString() }}">{{ \App\Support\NepaliDate::ago($storyDate) }}</time></p>
        @endif
    </div>
</article>
