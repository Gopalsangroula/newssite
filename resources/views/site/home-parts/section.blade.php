{{-- A category on the homepage. Takes $section with title, url, icon, layout and posts. --}}
<section class="hp-section">
    @include('site.home-parts.section-head', ['title' => $section->title, 'url' => $section->url, 'icon' => $section->icon])

    @if ($section->layout === 'feature')
        @php
            $first = $section->posts->first();
            $firstUrl = route('frontend.details.show', $first->slug);
        @endphp

        <div @class(['hp-feature', 'hp-feature--alone' => $section->posts->count() === 1])>
            <article class="hp-feature__main">
                @if ($first->cover_image)
                    <a class="hp-feature__media" href="{{ $firstUrl }}" tabindex="-1" aria-hidden="true">
                        <img src="{{ \App\Support\Thumb::url($first->cover_image, 1024) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                    </a>
                @endif
                <div class="hp-feature__body">
                    <h3><a href="{{ $firstUrl }}">{{ $first->title }}</a></h3>
                    @if ($first->excerpt !== '')
                        <p>{{ Str::limit($first->excerpt, 150) }}</p>
                    @endif
                </div>
            </article>

            @if ($section->posts->count() > 1)
                <div class="hp-feature__list">
                    @foreach ($section->posts->slice(1) as $post)
                        @include('site.home-parts.story', ['post' => $post, 'isRow' => true])
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="hp-cards">
            @foreach ($section->posts as $post)
                @include('site.home-parts.story', ['post' => $post])
            @endforeach
        </div>
    @endif
</section>
