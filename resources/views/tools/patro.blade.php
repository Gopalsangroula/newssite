@use('App\Support\NepaliDate')

<x-tool-page heading="नेपाली पात्रो" source="चाडपर्व र बिदा: Google को सार्वजनिक क्यालेन्डर र संस्थाले थपेका कार्यक्रम। चन्द्र तिथिका पर्वको मिति कहिलेकाहीँ एक दिन फरक पर्न सक्छ।">
    @php
        $monthName = NepaliDate::monthNames()[$month - 1];
        $patroUrl = fn ($date) => route('frontend.tools.patro', ['year' => $date[0], 'month' => $date[1]]);
        $eventsByDay = $events->groupBy(fn ($event) => $event->event_date->toDateString());
    @endphp

    <div class="tool-patro__head">
        @if ($previous)
            <a href="{{ $patroUrl($previous) }}" aria-label="अघिल्लो महिना"><span uk-icon="chevron-left"></span></a>
        @endif
        <h2>{{ $monthName }} {{ NepaliDate::digits($year) }}</h2>
        @if ($next)
            <a href="{{ $patroUrl($next) }}" aria-label="अर्को महिना"><span uk-icon="chevron-right"></span></a>
        @endif
    </div>
    <p class="tool-lead">{{ $firstDay->format('M Y') }} / {{ $firstDay->copy()->addDays($length - 1)->format('M Y') }}</p>

    <div class="tool-patro" role="table" aria-label="{{ $monthName }} {{ NepaliDate::digits($year) }}">
        @foreach (NepaliDate::weekdayNames() as $weekday)
            <div class="tool-patro__weekday" role="columnheader">{{ mb_substr($weekday, 0, -3) }}</div>
        @endforeach

        @for ($blank = 0; $blank < $firstDay->dayOfWeek; $blank++)
            <div class="tool-patro__blank"></div>
        @endfor

        @for ($day = 1; $day <= $length; $day++)
            @php
                $date = $firstDay->copy()->addDays($day - 1);
                $dayEvents = $eventsByDay->get($date->toDateString(), collect());
                $isToday = $today === ['year' => $year, 'month' => $month, 'day' => $day];
            @endphp
            <div @class([
                'tool-patro__day',
                'is-today' => $isToday,
                'is-holiday' => $date->isSaturday() || $dayEvents->contains('is_holiday', true),
                'has-event' => $dayEvents->isNotEmpty(),
            ]) role="cell">
                <b>{{ NepaliDate::digits($day) }}</b>
                <span>{{ $date->format('j') === '1' ? $date->format('M j') : $date->format('j') }}</span>
                @if ($dayEvents->isNotEmpty())
                    <small>{{ $dayEvents->pluck('name')->implode(' / ') }}</small>
                @endif
            </div>
        @endfor
    </div>

    @if ($events->isNotEmpty())
        <h2 class="tool-subhead">{{ $monthName }}का चाडपर्व र बिदा</h2>
        <ul class="tool-events">
            @foreach ($events as $event)
                <li>
                    <time class="tool-events__day" datetime="{{ $event->event_date->toDateString() }}">
                        {{ NepaliDate::digits(NepaliDate::parts($event->event_date)['day']) }}
                    </time>
                    <span class="tool-events__name">{{ $event->name }}</span>
                    @if ($event->is_holiday)
                        <span class="tool-events__tag">बिदा</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-tool-page>
