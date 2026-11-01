@if ($videos->isNotEmpty())
    <section class="hp-videos">
        <div class="hp-wrap">
            @include('site.home-parts.section-head', ['title' => 'भिडियो', 'url' => '/video', 'icon' => 'video-camera'])

            <div class="hp-videos__row">
                @foreach ($videos as $post)
                    @php
                        preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:[^"\s<]*&(?:amp;)?)?v=|embed/|shorts/|live/))([\w-]{11})~i', (string) $post->content, $youtube);
                        $thumbnail = $post->cover_image
                            ? \App\Support\Thumb::url($post->cover_image, 640)
                            : (isset($youtube[1]) ? 'https://i.ytimg.com/vi/'.$youtube[1].'/hqdefault.jpg' : null);
                    @endphp

                    <article class="hp-video">
                        <a class="hp-video__media" href="{{ route('frontend.details.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
                            @if ($thumbnail)
                                <img src="{{ $thumbnail }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                            <span class="hp-video__play"></span>
                        </a>
                        <h3 class="hp-video__title"><a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a></h3>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
