{{-- Posts ticked "Breaking news", under the lead stories. With more than one, a timeline runs beside them
     as on a live page: pressing a time scrolls to that story. Phones get the newest story, then the times. --}}
@if ($breaking->isNotEmpty())
    <section @class(['hp-wrap', 'hp-breaking', 'hp-breaking--single' => $breaking->count() === 1]) aria-labelledby="hp-breaking-title">
        <div class="hp-breaking__box">
            <h2 class="hp-breaking__bar" id="hp-breaking-title"><span class="hp-breaking__pulse" aria-hidden="true"></span>ब्रेकिङ न्यूज</h2>

            <div class="hp-breaking__body">
                @if ($breaking->count() > 1)
                    <nav class="hp-breaking__timeline" aria-label="ब्रेकिङ न्यूजका अपडेट">
                        <p class="hp-breaking__live"><span class="hp-breaking__pulse" aria-hidden="true"></span>लाइभ अपडेट</p>

                        <ol>
                            @foreach ($breaking as $post)
                                @php
                                    $storyDate = $post->published_at ?? $post->created_at;
                                @endphp

                                <li>
                                    <a href="{{ route('frontend.details.show', $post->slug) }}" data-breaking-story="hp-breaking-{{ $post->id }}" @if ($loop->first) aria-current="true" @endif>
                                        @if ($storyDate)
                                            <time class="hp-breaking__when" datetime="{{ $storyDate->toIso8601String() }}"><span uk-icon="icon: clock; ratio: 0.7"></span>{{ \App\Support\NepaliDate::ago($storyDate) }}</time>
                                        @endif
                                        <span class="hp-breaking__headline">{{ $post->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                <div class="hp-breaking__feed" data-breaking-feed>
                    @foreach ($breaking as $post)
                        @php
                            $storyUrl = route('frontend.details.show', $post->slug);
                            $storyDate = $post->published_at ?? $post->created_at;
                        @endphp

                        <article class="hp-breaking__story" id="hp-breaking-{{ $post->id }}">
                            @if ($storyDate)
                                <p class="hp-breaking__time"><span uk-icon="icon: clock; ratio: 0.7"></span><time datetime="{{ $storyDate->toIso8601String() }}">{{ \App\Support\NepaliDate::ago($storyDate) }}</time></p>
                            @endif

                            @if ($post->video)
                                @include('site.partials.video-player', ['post' => $post, 'class' => 'hp-breaking__media'])
                            @elseif ($post->cover_image)
                                <a class="hp-breaking__media" href="{{ $storyUrl }}" tabindex="-1" aria-hidden="true">
                                    <img src="{{ \App\Support\Thumb::url($post->cover_image, 1024) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                                </a>
                            @endif

                            <div class="hp-breaking__text">
                                <div class="hp-breaking__head">
                                    <h3 class="hp-breaking__title"><a href="{{ $storyUrl }}">{{ $post->title }}</a></h3>
                                    @include('site.partials.share-bar', ['shareUrl' => $storyUrl, 'shareTitle' => $post->title])
                                </div>

                                @if ($post->summary(320) !== '')
                                    <p class="hp-breaking__excerpt">{{ $post->summary(320) }}</p>
                                @endif

                                <a class="hp-breaking__more" href="{{ $storyUrl }}">पूरा समाचार</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
