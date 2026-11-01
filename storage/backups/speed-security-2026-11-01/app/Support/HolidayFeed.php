<?php

namespace App\Support;

use App\Models\PatroEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Festivals and holidays for the patro, from Google's public calendar of Nepal. They are
 * copied into patro_events, where staff can correct them or add their own.
 */
class HolidayFeed
{
    private const URL = 'https://calendar.google.com/calendar/ical/en.np%23holiday%40group.v.calendar.google.com/public/basic.ics';

    private const TIMEOUT_SECONDS = 10;

    /**
     * The calendar is in English. A name not listed here is kept as it comes.
     */
    private const NEPALI_NAMES = [
        'Basant Panchami' => 'वसन्त पञ्चमी',
        'Bhai Tika (Tihar)' => 'भाइटीका',
        'Buddha Jayanti' => 'बुद्ध जयन्ती',
        'Chhat Parwa' => 'छठ पर्व',
        'Christmas Day' => 'क्रिसमस',
        'Civil Service and School Holiday' => 'निजामती तथा विद्यालय बिदा',
        'Civil Service Day' => 'निजामती सेवा दिवस',
        'Constitution Day' => 'संविधान दिवस',
        'Astami (Dashain)' => 'महाअष्टमी',
        'Dashami (Dashain)' => 'विजया दशमी',
        'Duwadashi (Dashain)' => 'द्वादशी (दशैं)',
        'Edul Aajaha' => 'इद-उल-अजहा',
        'Ekadashi (Dashain)' => 'एकादशी (दशैं)',
        'Election Day' => 'निर्वाचन दिवस',
        'Election Holiday' => 'निर्वाचन बिदा',
        'Elimination of Caste Discrimination and Untouchability Day' => 'जातीय भेदभाव तथा छुवाछुत उन्मूलन दिवस',
        'Gai Jatra' => 'गाईजात्रा',
        'Gai Tihar' => 'गाई तिहार',
        'Ganatantra Diwas' => 'गणतन्त्र दिवस',
        'Gaura Parba' => 'गौरा पर्व',
        'Gen Z Martyr’s Day' => 'जेन जी शहीद दिवस',
        'General Election Day' => 'आम निर्वाचन दिवस',
        'General Election Holiday' => 'आम निर्वाचन बिदा',
        'Ghatasthapana' => 'घटस्थापना',
        'Gobhardan Pujan (Tihar)' => 'गोवर्धन पूजा',
        'Guru Nanak Jayanti' => 'गुरु नानक जयन्ती',
        'Gyalpo Losar' => 'ग्याल्पो ल्होसार',
        'Hartalika Teej' => 'हरितालिका तीज',
        'Holiday for Emir of Qatar\'s Visit' => 'कतारका अमिरको भ्रमण बिदा',
        'International Day of Disabled Persons' => 'अन्तर्राष्ट्रिय अपाङ्गता दिवस',
        'Janai Purnima' => 'जनै पूर्णिमा',
        'Jitiya Parwa' => 'जितिया पर्व',
        'Laxmi Puja (Tihar)' => 'लक्ष्मी पूजा',
        'Maghe Sankranti' => 'माघे सङ्क्रान्ति',
        'Maha Shivaratri' => 'महाशिवरात्रि',
        'Majdoor Divas' => 'मजदुर दिवस',
        'Martyrs\' Memorial Day' => 'शहीद दिवस',
        'Nari Dibas' => 'अन्तर्राष्ट्रिय नारी दिवस',
        'National Democracy Day' => 'प्रजातन्त्र दिवस',
        'National Mourning Day' => 'राष्ट्रिय शोक दिवस',
        'Nawami (Dashain)' => 'महानवमी',
        'Nepali New Year' => 'नेपाली नयाँ वर्ष',
        'People\'s War Day' => 'जनयुद्ध दिवस',
        'Phalgunanda Jayanti' => 'फाल्गुनन्द जयन्ती',
        'Phulpati (Dashain)' => 'फूलपाती',
        'Prithvi Jayanti' => 'पृथ्वी जयन्ती',
        'Public Holiday for Extreme Weather' => 'प्रतिकूल मौसमको बिदा',
        'Ram Nawami' => 'राम नवमी',
        'Ramadan Start' => 'रमजान सुरु',
        'Ramjan Edul Fikra' => 'इद-उल-फित्र',
        'Shree Krishna Janamashtami' => 'श्रीकृष्ण जन्माष्टमी',
        'Sonam Losar' => 'सोनाम ल्होसार',
        'Tamu Lhosar' => 'तमु ल्होसार',
        'Tamu Lhosar Holiday' => 'तमु ल्होसार बिदा',
        'Tihar Holiday' => 'तिहार बिदा',
        'Ubhauli Parva' => 'उभौली पर्व',
        'Udhauli Parva' => 'उधौली पर्व',
    ];

    /**
     * Adds events that are new and returns how many. Events already here are left
     * alone, so a correction or a deletion made in the admin survives the next import.
     */
    public static function import(): int
    {
        $calendar = Http::timeout(self::TIMEOUT_SECONDS)->get(self::URL)->throw()->body();
        $added = 0;

        foreach (static::parse($calendar) as $event) {
            $added += (int) PatroEvent::withTrashed()->firstOrCreate(['uid' => $event['uid']], $event)->wasRecentlyCreated;
        }

        return $added;
    }

    /**
     * At most once a day, after the page has gone out, so that no reader waits for it.
     */
    public static function refreshInBackground(): void
    {
        if (! Cache::add('patro.feed-checked', true, now()->addDay())) {
            return;
        }

        app()->terminating(function () {
            try {
                static::import();
            } catch (Throwable $e) {
                Log::warning('Holiday calendar import failed', ['error' => $e->getMessage()]);
            }
        });
    }

    /**
     * The events in an iCalendar file. Long lines are folded onto indented continuation lines.
     */
    public static function parse(string $calendar): array
    {
        $unfolded = preg_replace('/\r?\n[ \t]/', '', $calendar);
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $unfolded, $blocks);

        $events = [];

        foreach ($blocks[1] as $block) {
            $field = fn (string $pattern) => preg_match($pattern, $block, $match) ? trim($match[1]) : null;

            $uid = $field('/^UID:(.*)$/m');
            $date = $field('/^DTSTART;VALUE=DATE:(\d{8})/m');
            $name = str_replace(['\\,', '\\;'], [',', ';'], (string) $field('/^SUMMARY:(.*)$/m'));

            if ($uid && $date && $name !== '') {
                $events[] = [
                    'uid' => $uid,
                    'event_date' => Carbon::createFromFormat('!Ymd', $date)->toDateString(),
                    'name' => self::NEPALI_NAMES[$name] ?? $name,
                    'is_holiday' => str_starts_with((string) $field('/^DESCRIPTION:(.*)$/m'), 'Public holiday'),
                ];
            }
        }

        return $events;
    }
}
