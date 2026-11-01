{{-- One person on a committee page. Takes $entry with post, role, name and location. --}}
<article class="lb-person">
    <div class="lb-person__photo">
        @if($entry->post->cover_image)
            <img src="{{ \App\Support\Thumb::url($entry->post->cover_image, 640) }}" alt="{{ $entry->name }}"
                 loading="lazy" decoding="async" onerror="this.remove()">
        @endif
    </div>

    @if($entry->role)
        <p class="lb-person__role">{{ $entry->role }}</p>
    @endif

    <h2 class="lb-person__name">
        <a href="{{ route('frontend.details.show', $entry->post->slug) }}">{{ $entry->name }}</a>
    </h2>

    @if($entry->location)
        <p class="lb-person__location">{{ $entry->location }}</p>
    @endif
</article>
