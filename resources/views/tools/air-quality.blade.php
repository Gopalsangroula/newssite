<x-tool-page heading="वायु गुणस्तर" source="स्रोत: Open-Meteo.com (CC BY 4.0)। अनुमानित मान, US AQI मापदण्डअनुसार।">
    @php
        $digits = fn ($value) => \App\Support\NepaliDate::digits($value);
    @endphp

    @if ($places)
        <div class="tool-grid">
            @foreach ($places as $place)
                @php
                    $band = \App\Support\LiveData::airBand($place['aqi']);
                @endphp
                <article class="tool-card tool-card--plain">
                    <strong>{{ $place['name'] }}</strong>
                    <span class="tool-card__figure">{{ $digits($place['aqi']) }}</span>
                    <span class="tool-band tool-band--{{ $band['style'] }}">{{ $band['name'] }}</span>
                    <dl class="tool-facts">
                        <div><dt>PM2.5</dt><dd>{{ $digits($place['pm25']) }}</dd></div>
                        <div><dt>PM10</dt><dd>{{ $digits($place['pm10']) }}</dd></div>
                    </dl>
                </article>
            @endforeach
        </div>

        <h2 class="tool-subhead">सूचकाङ्कको अर्थ</h2>
        <div class="tool-table">
            <table>
                <thead>
                    <tr>
                        <th>AQI</th>
                        <th>अवस्था</th>
                        <th>स्वास्थ्यका लागि</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $from = 0;
                    @endphp
                    @foreach (\App\Support\LiveData::airBands() as $top => [$name, $style, $advice])
                        <tr>
                            <td>{{ $top === PHP_INT_MAX ? $digits($from).'+' : $digits($from).'-'.$digits($top) }}</td>
                            <td><span class="tool-band tool-band--{{ $style }}">{{ $name }}</span></td>
                            <td>{{ $advice }}</td>
                        </tr>
                        @php
                            $from = $top + 1;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        @include('tools.unavailable')
    @endif
</x-tool-page>
