@if ($leads->isNotEmpty())
    <section class="hp-wrap hp-leads" aria-label="मुख्य समाचार">
        @foreach ($leads as $lead)
            @php
                $leadUrl = route('frontend.details.show', $lead->slug);
                $leadDate = $lead->published_at ?? $lead->created_at;
            @endphp

            <article class="hp-lead">
                @if ($lead->kicker)
                    <p class="hp-kicker hp-kicker--pill">{{ $lead->kicker }}</p>
                @endif

                <h2 class="hp-lead__title"><a href="{{ $leadUrl }}">{{ $lead->title }}</a></h2>

                @if ($leadDate)
                    <p class="hp-meta"><time datetime="{{ $leadDate->toDateString() }}">{{ \App\Support\NepaliDate::ago($leadDate) }}</time></p>
                @endif

                @if ($lead->video)
                    @include('site.partials.video-player', ['post' => $lead, 'class' => 'hp-lead__video', 'isFirst' => $loop->first])
                @elseif ($lead->cover_image)
                    <a class="hp-lead__media" href="{{ $leadUrl }}" tabindex="-1" aria-hidden="true">
                        <img src="{{ \App\Support\Thumb::url($lead->cover_image, 1024) }}" alt="" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async" onerror="this.remove()">
                    </a>
                @endif

                @if ($lead->summary(190) !== '')
                    <p class="hp-lead__excerpt">{{ $lead->summary(190) }}</p>
                @endif
            </article>
        @endforeach
    </section>
@endif
