<div>
    <nav>
        <a href="{{ $homeUrl }}">← Home</a> / {{ $tag->name }}
    </nav>

    <h1>Posts tagged {{ $tag->name }}</h1>

    @forelse($posts as $post)
        <article wire:key="{{ $post->id }}">
            <h2>{{ $post->title }}</h2>
            <a href="{{ route('frontend.details.show', $post->slug) }}">Read More</a>
        </article>
    @empty
        <p>No posts.</p>
    @endforelse

    {{ $posts->links() }}
</div>