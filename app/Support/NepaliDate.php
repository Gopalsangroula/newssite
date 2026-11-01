<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Bikram Sambat dates for the public pages. The calendar follows the yearly almanac, so
 * month lengths have to come from a table. A date outside the table stays in English.
 */
class NepaliDate
{
    /**
     * 1 Baisakh of the first year in the table.
     */
    private const FIRST_DAY = '2018-04-14';

    private const MONTHS = ['बैशाख', 'जेठ', 'असार', 'साउन', 'भदौ', 'असोज', 'कार्तिक', 'मंसिर', 'पुस', 'माघ', 'फागुन', 'चैत'];

    private const WEEKDAYS = ['आइतबार', 'सोमबार', 'मंगलबार', 'बुधबार', 'बिहीबार', 'शुक्रबार', 'शनिबार'];

    private const DIGITS = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

    /**
     * Days in each month. 2084 onwards is a forecast: replace each year when its almanac is published.
     */
    private const YEARS = [
        2075 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2076 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2077 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2078 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2079 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2080 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2081 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2082 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2083 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2084 => [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        2085 => [31, 32, 31, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        2086 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2087 => [31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30],
        2088 => [30, 31, 32, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        2089 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2090 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
    ];

    /**
     * The year, month (1-12) and day of a date, or null when it is outside the table.
     */
    public static function parts(CarbonInterface $date): ?array
    {
        $firstDay = Carbon::parse(self::FIRST_DAY);
        $days = (int) round($firstDay->diffInDays($date->copy()->timezone($firstDay->timezone)->startOfDay(), false));

        if ($days < 0) {
            return null;
        }

        foreach (self::YEARS as $year => $months) {
            foreach ($months as $index => $length) {
                if ($days < $length) {
                    return ['year' => $year, 'month' => $index + 1, 'day' => $days + 1];
                }

                $days -= $length;
            }
        }

        return null;
    }

    /**
     * The Gregorian date of a Bikram Sambat date, or null when the table has no such day.
     */
    public static function toGregorian(int $year, int $month, int $day): ?Carbon
    {
        $length = self::YEARS[$year][$month - 1] ?? 0;

        if ($day < 1 || $day > $length) {
            return null;
        }

        $days = $day - 1 + array_sum(array_slice(self::YEARS[$year], 0, $month - 1));

        foreach (self::YEARS as $earlierYear => $months) {
            if ($earlierYear < $year) {
                $days += array_sum($months);
            }
        }

        return Carbon::parse(self::FIRST_DAY)->addDays($days);
    }

    /**
     * Days in a month, or 0 when the table does not have it.
     */
    public static function monthLength(int $year, int $month): int
    {
        return self::YEARS[$year][$month - 1] ?? 0;
    }

    public static function monthNames(): array
    {
        return self::MONTHS;
    }

    /**
     * Sunday first, the way a Nepali calendar page runs.
     */
    public static function weekdayNames(): array
    {
        return self::WEEKDAYS;
    }

    /**
     * The years the table covers.
     */
    public static function years(): array
    {
        return array_keys(self::YEARS);
    }

    /**
     * १५ कार्तिक २०८३
     */
    public static function format(CarbonInterface $date): string
    {
        $parts = static::parts($date);

        return $parts
            ? static::digits($parts['day']).' '.self::MONTHS[$parts['month'] - 1].' '.static::digits($parts['year'])
            : $date->format('M j, Y');
    }

    /**
     * १५ कार्तिक २०८३, आइतबार
     */
    public static function full(CarbonInterface $date): string
    {
        return static::format($date).', '.self::WEEKDAYS[$date->dayOfWeek];
    }

    /**
     * १५ कार्तिक २०८३, आइतबार ११ : ०५ बजे
     */
    public static function withTime(CarbonInterface $date): string
    {
        return static::full($date).' '.static::digits($date->format('H : i')).' बजे';
    }

    /**
     * The day and month on their own, for a date chip.
     */
    public static function dayAndMonth(CarbonInterface $date): array
    {
        $parts = static::parts($date);

        return $parts
            ? ['day' => static::digits($parts['day']), 'month' => self::MONTHS[$parts['month'] - 1]]
            : ['day' => $date->format('j'), 'month' => $date->format('M')];
    }

    /**
     * How long ago, for anything in the last week. Older dates are printed in full.
     */
    public static function ago(CarbonInterface $date): string
    {
        $minutes = (int) $date->diffInMinutes(now());

        return match (true) {
            $minutes < 1 => 'भर्खरै',
            $minutes < 60 => static::digits($minutes).' मिनेट अगाडि',
            $minutes < 1440 => static::digits(intdiv($minutes, 60)).' घण्टा अगाडि',
            $minutes < 10080 => static::digits(intdiv($minutes, 1440)).' दिन अगाडि',
            default => static::format($date),
        };
    }

    public static function digits(int|string $value): string
    {
        return str_replace(range(0, 9), self::DIGITS, (string) $value);
    }
}
