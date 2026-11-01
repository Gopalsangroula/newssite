@if ($notices->isNotEmpty())
    <section class="hp-section">
        @include('site.home-parts.section-head', ['title' => 'सूचना', 'url' => '/notice', 'icon' => 'bell'])

        <ul class="hp-board">
            @foreach ($notices as $post)
                @php
                    $noticeDate = $post->published_at ?? $post->created_at;
                    $noticeDay = \App\Support\NepaliDate::dayAndMonth($noticeDate);
                @endphp
                <li>
                    <time class="hp-board__date" datetime="{{ $noticeDate->toDateString() }}">
                        <b>{{ $noticeDay['day'] }}</b>{{ $noticeDay['month'] }}
                    </time>
                    <a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
