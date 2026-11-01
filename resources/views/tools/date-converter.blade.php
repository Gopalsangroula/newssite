@use('App\Support\NepaliDate')

<x-tool-page heading="मिति परिवर्तक">
    <div class="tool-convert">
        <form class="tool-card tool-card--plain" action="{{ route('frontend.tools.converter') }}" method="get">
            <strong>वि.सं. बाट ई.सं.</strong>
            <div class="tool-convert__fields">
                <label>वर्ष
                    <select class="uk-select" name="bs_year">
                        @foreach (NepaliDate::years() as $year)
                            <option value="{{ $year }}" @selected($bs['year'] === $year)>{{ NepaliDate::digits($year) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>महिना
                    <select class="uk-select" name="bs_month">
                        @foreach (NepaliDate::monthNames() as $index => $name)
                            <option value="{{ $index + 1 }}" @selected($bs['month'] === $index + 1)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>गते
                    <select class="uk-select" name="bs_day">
                        @for ($day = 1; $day <= 32; $day++)
                            <option value="{{ $day }}" @selected($bs['day'] === $day)>{{ NepaliDate::digits($day) }}</option>
                        @endfor
                    </select>
                </label>
            </div>
            <input type="hidden" name="ad" value="{{ $ad }}">
            <button class="tool-button" type="submit">बदल्नुहोस्</button>

            @if ($bsResult)
                <p class="tool-result">{{ $bsResult->format('l, F j, Y') }}</p>
            @else
                <p class="tool-result tool-result--error">त्यो महिनामा यो गते छैन।</p>
            @endif
        </form>

        <form class="tool-card tool-card--plain" action="{{ route('frontend.tools.converter') }}" method="get">
            <strong>ई.सं. बाट वि.सं.</strong>
            <div class="tool-convert__fields">
                <label>मिति
                    <input class="uk-input" type="date" name="ad" value="{{ $ad }}">
                </label>
            </div>
            <input type="hidden" name="bs_year" value="{{ $bs['year'] }}">
            <input type="hidden" name="bs_month" value="{{ $bs['month'] }}">
            <input type="hidden" name="bs_day" value="{{ $bs['day'] }}">
            <button class="tool-button" type="submit">बदल्नुहोस्</button>

            @if ($adResult)
                <p class="tool-result">{{ NepaliDate::full($adResult) }}</p>
            @else
                <p class="tool-result tool-result--error">
                    {{ NepaliDate::digits(NepaliDate::years()[0]) }} देखि {{ NepaliDate::digits(last(NepaliDate::years())) }} वि.सं. भित्रको मिति मात्र बदल्न सकिन्छ।
                </p>
            @endif
        </form>
    </div>
</x-tool-page>
