@if ($special->isNotEmpty())
    @php
        // The newest story sits in the middle, with two beside it and the rest as a list.
        $centre = $special->first();
        $centreUrl = route('frontend.details.show', $centre->slug);
    @endphp

    <section class="hp-special">
        <div class="hp-wrap">
            @include('site.home-parts.section-head', ['title' => $categories->get('bishesh')?->name ?? 'विशेष', 'url' => '/bishesh', 'icon' => 'star'])

            <div class="hp-special__grid">
                <div class="hp-special__side">
                    @foreach ($special->slice(1, 2) as $post)
                        @include('site.home-parts.story', ['post' => $post])
                    @endforeach
                </div>

                <article class="hp-special__centre">
                    @if ($centre->cover_image)
                        <a class="hp-feature__media" href="{{ $centreUrl }}" tabindex="-1" aria-hidden="true">
                            <img src="{{ \App\Support\Thumb::url($centre->cover_image, 1024) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                        </a>
                    @endif
                    <h3><a href="{{ $centreUrl }}">{{ $centre->title }}</a></h3>
                    @if ($centre->summary(190) !== '')
                        <p>{{ $centre->summary(190) }}</p>
                    @endif
                </article>

                <div class="hp-special__list">
                    @foreach ($special->slice(3) as $post)
                        @include('site.home-parts.story', ['post' => $post, 'isRow' => true])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
