<?php

namespace App\Http\Controllers;

use App\Models\MetalRate;
use App\Models\PatroEvent;
use App\Models\Rashifal;
use App\Support\HolidayFeed;
use App\Support\LiveData;
use App\Support\NepaliDate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.index');
    }

    public function forex()
    {
        return view('tools.forex', ['forex' => LiveData::forex()]);
    }

    public function goldSilver()
    {
        return view('tools.gold-silver', [
            'rates' => MetalRate::latest('rate_date')->take(7)->get(),
        ]);
    }

    public function weather()
    {
        return view('tools.weather', ['places' => LiveData::weather()]);
    }

    public function airQuality()
    {
        return view('tools.air-quality', ['places' => LiveData::airQuality()]);
    }

    public function patro(Request $request)
    {
        $today = NepaliDate::parts(now());
        $year = (int) $request->query('year', $today['year'] ?? 0);
        $month = (int) $request->query('month', $today['month'] ?? 1);

        if (NepaliDate::monthLength($year, $month) === 0) {
            [$year, $month] = [$today['year'], $today['month']];
        }

        $previous = $month === 1 ? [$year - 1, 12] : [$year, $month - 1];
        $next = $month === 12 ? [$year + 1, 1] : [$year, $month + 1];
        $firstDay = NepaliDate::toGregorian($year, $month, 1);
        $length = NepaliDate::monthLength($year, $month);

        HolidayFeed::refreshInBackground();

        return view('tools.patro', [
            'year' => $year,
            'month' => $month,
            'today' => $today,
            'firstDay' => $firstDay,
            'length' => $length,
            'events' => PatroEvent::whereBetween('event_date', [$firstDay->toDateString(), $firstDay->copy()->addDays($length - 1)->toDateString()])
                ->orderBy('event_date')
                ->orderByDesc('is_holiday')
                ->get(),
            'previous' => NepaliDate::monthLength(...$previous) ? $previous : null,
            'next' => NepaliDate::monthLength(...$next) ? $next : null,
        ]);
    }

    /**
     * The newest rashifal up to today, so a day written in advance stays hidden until it comes.
     */
    public function rashifal()
    {
        return view('tools.rashifal', [
            'rashifal' => Rashifal::whereDate('rashifal_date', '<=', today())->latest('rashifal_date')->first(),
        ]);
    }

    public function dateConverter(Request $request)
    {
        $today = NepaliDate::parts(now());
        $bs = [
            'year' => (int) $request->query('bs_year', $today['year']),
            'month' => (int) $request->query('bs_month', $today['month']),
            'day' => (int) $request->query('bs_day', $today['day']),
        ];
        $ad = (string) $request->query('ad', now()->toDateString());

        $bsResult = NepaliDate::toGregorian($bs['year'], $bs['month'], $bs['day']);
        $adDate = rescue(fn () => Carbon::createFromFormat('!Y-m-d', $ad), null, false);
        $adResult = $adDate && NepaliDate::parts($adDate) ? $adDate : null;

        return view('tools.date-converter', compact('bs', 'ad', 'bsResult', 'adResult'));
    }
}
