@if ($leads->isNotEmpty())
    <section class="hp-wrap hp-leads" aria-label="मुख्य समाचार">
        @foreach ($leads as $lead)
            @php
                $leadUrl = route('frontend.details.show', $lead->slug);
                $leadDate = $lead->published_at ?? $lead->created_at;
            @endphp

            <article class="hp-lead">
                @if ($lead->category)
                    <a class="hp-kicker hp-kicker--pill" href="{{ route('frontend.category.show', $lead->category->slug) }}">{{ $lead->category->name }}</a>
                @endif

                <h2 class="hp-lead__title"><a href="{{ $leadUrl }}">{{ $lead->title }}</a></h2>

                @if ($leadDate)
                    <p class="hp-meta"><time datetime="{{ $leadDate->toDateString() }}">{{ \App\Support\NepaliDate::ago($leadDate) }}</time></p>
                @endif

                @if ($lead->cover_image)
                    <a class="hp-lead__media" href="{{ $leadUrl }}" tabindex="-1" aria-hidden="true">
                        <img src="{{ Storage::url($lead->cover_image) }}" alt="" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async" onerror="this.remove()">
                    </a>
                @endif

                @if ($lead->excerpt !== '')
                    <p class="hp-lead__excerpt">{{ Str::limit($lead->excerpt, 190) }}</p>
                @endif
            </article>
        @endforeach
    </section>
@endif
