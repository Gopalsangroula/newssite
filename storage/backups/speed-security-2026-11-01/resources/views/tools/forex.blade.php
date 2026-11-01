<x-tool-page heading="विनिमय दर" source="स्रोत: नेपाल राष्ट्र बैंक। दर नेपाली रुपैयाँमा।">
    @if ($forex)
        <p class="tool-lead">मिति: {{ \App\Support\NepaliDate::format(\Illuminate\Support\Carbon::parse($forex['date'])) }}</p>

        <div class="tool-table">
            <table>
                <thead>
                    <tr>
                        <th>मुद्रा</th>
                        <th>एकाइ</th>
                        <th>खरिद</th>
                        <th>बिक्री</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($forex['rates'] as $rate)
                        <tr>
                            <td><strong>{{ $rate['code'] }}</strong> {{ $rate['name'] }}</td>
                            <td>{{ $rate['unit'] }}</td>
                            <td>{{ $rate['buy'] }}</td>
                            <td>{{ $rate['sell'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        @include('tools.unavailable')
    @endif
</x-tool-page>
