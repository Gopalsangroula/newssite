@use('App\Models\Rashifal')
@use('App\Support\NepaliDate')

<x-tool-page heading="राशिफल">
    @if ($rashifal)
        <p class="tool-lead">
            मिति: {{ NepaliDate::full($rashifal->rashifal_date) }}
            @unless ($rashifal->rashifal_date->isToday())
                (आजको राशिफल राखिएको छैन, यो पछिल्लो राखिएको राशिफल हो)
            @endunless
            @if ($rashifal->author)
                | {{ $rashifal->author }}
            @endif
        </p>

        <div class="tool-rashi">
            @foreach (Rashifal::SIGNS as $key => $sign)
                <article class="tool-rashi__sign">
                    {{-- The variation selector keeps the symbol as plain text, not a coloured emoji. --}}
                    <span class="tool-rashi__symbol" aria-hidden="true">{{ $sign['symbol'] }}&#xFE0E;</span>
                    <div>
                        <h2>{{ $sign['name'] }}</h2>
                        <p class="tool-rashi__letters">{{ $sign['letters'] }}</p>
                        <p>{{ $rashifal->readings[$key] ?? '' }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <p class="tool-note">राशिफल अहिलेसम्म राखिएको छैन।</p>
    @endif
</x-tool-page>
