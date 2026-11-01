@php
    $lead = $topStories->first();
    $leadDate = $lead?->published_at ?? $lead?->created_at;
    $showPopular = $popularPosts->count() >= 3;
@endphp

@if ($lead)
    <section class="hp-top" aria-label="मुख्य समाचार">
        <div class="hp-wrap">
            <article class="hp-lead">
                @if ($lead->category)
                    <a class="hp-kicker" href="{{ route('frontend.category.show', $lead->category->slug) }}">{{ $lead->category->name }}</a>
                @endif

                <h2 class="hp-lead__title"><a href="{{ route('frontend.details.show', $lead->slug) }}">{{ $lead->title }}</a></h2>

                @if ($lead->excerpt !== '')
                    <p class="hp-lead__excerpt">{{ Str::limit($lead->excerpt, 190) }}</p>
                @endif

                @if ($leadDate)
                    <p class="hp-meta"><time datetime="{{ $leadDate->toDateString() }}">{{ \App\Support\NepaliDate::ago($leadDate) }}</time></p>
                @endif
            </article>

            <div @class(['hp-top__grid', 'hp-top__grid--text' => ! $lead->cover_image])>
                @if ($lead->cover_image)
                    <a class="hp-lead__media" href="{{ route('frontend.details.show', $lead->slug) }}" tabindex="-1" aria-hidden="true">
                        <img src="{{ Storage::url($lead->cover_image) }}" alt="" fetchpriority="high" decoding="async" onerror="this.remove()">
                    </a>
                @endif

                <aside class="hp-rail" aria-label="ताजा र लोकप्रिय">
                    <ul class="hp-rail__tabs" uk-switcher="connect: #hp-rail-lists; animation: uk-animation-fade">
                        <li class="uk-active"><a href="#">ताजा</a></li>
                        @if ($showPopular)
                            <li><a href="#">लोकप्रिय</a></li>
                        @endif
                    </ul>

                    <div id="hp-rail-lists" class="uk-switcher">
                        <ol class="hp-rank uk-active">
                            @foreach ($latestPosts as $post)
                                <li><a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a></li>
                            @endforeach
                        </ol>
                        @if ($showPopular)
                            <ol class="hp-rank">
                                @foreach ($popularPosts as $post)
                                    <li><a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a></li>
                                @endforeach
                            </ol>
                        @endif
                    </div>

                    @include('livewire.frontend.home-parts.ad-slot', ['placement' => 'sidebar'])
                </aside>
            </div>

            @if ($topStories->count() > 1)
                <div class="hp-cards">
                    @foreach ($topStories->slice(1) as $post)
                        @include('livewire.frontend.home-parts.story', ['post' => $post, 'showCategory' => true])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
