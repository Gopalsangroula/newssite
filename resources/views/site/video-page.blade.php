<div class="lb-page lb-page--video">
    @include('site.partials.page-head', [
        'title' => $category->name,
        'count' => $posts->isNotEmpty() ? $posts->count() . ' भिडियो' : null,
    ])

    <section class="lb-results">
        @if($posts->isNotEmpty())
            <div class="lb-videos">
                @foreach($posts as $post)
                    @php
                        $content = (string) $post->content;
                        preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:[^"\s<]*&(?:amp;)?)?v=|embed/|shorts/|live/))([\w-]{11})~i', $content, $youtube);
                        $videoId = $youtube[1] ?? null;
                        $videoDate = $post->published_at ?? $post->created_at;
                    @endphp

                    <article class="lb-video">
                        <div class="lb-video__media">
                            @if($post->cover_image)
                                <img src="{{ \App\Support\Thumb::url($post->cover_image, 640) }}" alt="{{ $post->title }}"
                                     loading="lazy" decoding="async" onerror="this.remove()">
                            @endif

                            @if($videoId)
                                {{-- YouTube loads only once someone presses play. Without JavaScript the link opens YouTube. --}}
                                <a class="lb-video__play"
                                   href="https://www.youtube.com/watch?v={{ $videoId }}"
                                   target="_blank" rel="noopener"
                                   data-video-id="{{ $videoId }}"
                                   aria-label="भिडियो हेर्नुहोस्: {{ $post->title }}">
                                    <span class="lb-video__button"></span>
                                </a>
                            @elseif(Str::contains($content, ['<iframe', '<video']))
                                {!! $content !!}
                            @endif
                        </div>

                        <div class="lb-video__body">
                            <h2 class="lb-video__title">
                                <a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            <p class="lb-card__meta">
                                @if($videoDate)
                                    <time datetime="{{ $videoDate->toDateString() }}">{{ \App\Support\NepaliDate::format($videoDate) }}</time>
                                @endif
                                @if($post->author?->id)
                                    <span>{{ $post->author->name }}</span>
                                @endif
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            @include('site.partials.empty-state', [
                'icon' => 'play-circle',
                'title' => 'अहिलेसम्म कुनै भिडियो प्रकाशित भएको छैन',
                'text' => 'नयाँ भिडियो प्रकाशित भएपछि यहाँ देखिनेछ।',
            ])
        @endif
    </section>
</div>
