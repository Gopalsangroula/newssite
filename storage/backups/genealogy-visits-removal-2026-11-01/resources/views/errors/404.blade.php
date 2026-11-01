@extends('errors.brand')

@section('code', '404')
@section('title', 'पृष्ठ फेला परेन')
@section('subtitle', 'Page not found')
@section('message', 'तपाईंले खोज्नुभएको पृष्ठ सारिएको, हटाइएको वा ठेगाना गलत हुन सक्छ। The page may have moved, or the address may be wrong.')

@section('more')
    @php
        // Helpful extras only: if these cannot load, the page still shows without them.
        try {
            $latestPosts = \App\Models\Post::published()->latest('published_at')->take(4)->get(['title', 'slug']);
            $topCategories = \App\Models\Category::whereNull('parent_id')->whereNotNull('slug')->take(5)->get(['name', 'slug']);
        } catch (\Throwable) {
            $latestPosts = collect();
            $topCategories = collect();
        }
    @endphp

    @unless (request()->is('admin', 'admin/*'))
        <div class="err-more">
            <p class="err-more-title">यहाँबाट हेर्नुहोस्</p>
            <ul class="err-links">
                <li><a href="{{ url('/genealogy') }}">वंशावली</a></li>
                @foreach ($topCategories as $category)
                    <li><a href="{{ url('/'.$category->slug) }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>

            @if ($latestPosts->isNotEmpty())
                <p class="err-more-title">पछिल्लो अपडेट</p>
                <ul class="err-posts">
                    @foreach ($latestPosts as $post)
                        <li><a href="{{ url('/details/'.$post->slug) }}">{{ $post->title }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endunless
@endsection
