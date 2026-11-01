<div class="lb-page lb-page--video">
    @include('livewire.frontend.partials.page-head', [
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

                    <article class="lb-video" wire:key="post-{{ $post->id }}">
                        <div class="lb-video__media">
                            @if($post->cover_image)
                                <img src="{{ Storage::url($post->cover_image) }}" alt="{{ $post->title }}"
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
            @include('livewire.frontend.partials.empty-state', [
                'icon' => 'play-circle',
                'title' => 'अहिलेसम्म कुनै भिडियो प्रकाशित भएको छैन',
                'text' => 'नयाँ भिडियो प्रकाशित भएपछि यहाँ देखिनेछ।',
            ])
        @endif
    </section>

    <script>
        document.addEventListener('click', function (event) {
            var link = event.target.closest('[data-video-id]');
            if (!link) return;

            event.preventDefault();

            var player = document.createElement('iframe');
            player.src = 'https://www.youtube-nocookie.com/embed/' + link.dataset.videoId + '?autoplay=1&rel=0';
            player.title = link.getAttribute('aria-label');
            player.allow = 'autoplay; encrypted-media; picture-in-picture';
            player.allowFullscreen = true;
            link.replaceWith(player);
        });
    </script>
</div>
