<x-tool-page heading="मौसम" source="स्रोत: Open-Meteo.com (CC BY 4.0)">
    @php
        $digits = fn ($value) => \App\Support\NepaliDate::digits($value);
    @endphp

    @if ($places)
        <div class="tool-grid">
            @foreach ($places as $place)
                <article class="tool-card tool-card--plain">
                    <strong>{{ $place['name'] }}</strong>
                    <span class="tool-card__figure">{{ $digits($place['temperature']) }}°C</span>
                    <span>{{ $place['summary'] }}</span>
                    <dl class="tool-facts">
                        <div><dt>अनुभूति</dt><dd>{{ $digits($place['feels_like']) }}°C</dd></div>
                        <div><dt>आर्द्रता</dt><dd>{{ $digits($place['humidity']) }}%</dd></div>
                        <div><dt>हावा</dt><dd>{{ $digits($place['wind']) }} कि.मी./घण्टा</dd></div>
                    </dl>
                </article>
            @endforeach
        </div>
    @else
        @include('tools.unavailable')
    @endif
</x-tool-page>
