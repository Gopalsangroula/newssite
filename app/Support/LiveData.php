<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Figures for the tools pages, from free public sources. A reader never waits for one of them:
 * the page shows the last good answer, and the source is asked after the page has gone out.
 */
class LiveData
{
    private const CONNECT_SECONDS = 3;
    private const TIMEOUT_SECONDS = 6;
    private const RETRY_MINUTES = 5;

    /**
     * Places on the weather and air quality pages: the branches, then the main cities nearby.
     */
    private const PLACES = [
        'काठमाडौं' => [27.7172, 85.3240],
        'बिर्तामोड, झापा' => [26.6437, 87.9899],
        'धरान, सुनसरी' => [26.8065, 87.2846],
        'फुङलिङ, ताप्लेजुङ' => [27.3509, 87.6697],
        'फिदिम, पाँचथर' => [27.1450, 87.7600],
        'खाँदबारी, संखुवासभा' => [27.3745, 87.2040],
        'विराटनगर' => [26.4525, 87.2718],
        'पोखरा' => [28.2096, 83.9856],
    ];

    /**
     * WMO weather codes, by the highest code each description covers.
     */
    private const WEATHER_CODES = [
        0 => 'सफा',
        2 => 'आंशिक बदली',
        3 => 'बदली',
        48 => 'कुहिरो',
        57 => 'सिमसिमे पानी',
        67 => 'वर्षा',
        77 => 'हिमपात',
        82 => 'झरी',
        86 => 'हिमपात',
        99 => 'मेघगर्जन',
    ];

    /**
     * US AQI bands: the top of each band, its name and what it means for health.
     */
    private const AIR_BANDS = [
        50 => ['राम्रो', 'good', 'वायु सफा छ, स्वास्थ्यमा खासै जोखिम छैन।'],
        100 => ['मध्यम', 'moderate', 'धेरैका लागि ठीक छ, अति संवेदनशील व्यक्तिले ध्यान दिनुपर्छ।'],
        150 => ['संवेदनशील समूहका लागि अस्वस्थकर', 'sensitive', 'बालबालिका, वृद्धवृद्धा र दमका बिरामीले बाहिरको समय घटाउनुहोस्।'],
        200 => ['अस्वस्थकर', 'unhealthy', 'सबैले बाहिरको लामो शारीरिक गतिविधि घटाउनुहोस्।'],
        300 => ['धेरै अस्वस्थकर', 'very-unhealthy', 'सकेसम्म घरभित्रै बस्नुहोस्।'],
        PHP_INT_MAX => ['खतरनाक', 'hazardous', 'सबैका लागि आपतकालीन स्वास्थ्य चेतावनी।'],
    ];

    /**
     * Today's Nepal Rastra Bank rates: ['date' => ..., 'rates' => [...]], or an empty array.
     */
    public static function forex(): array
    {
        return static::cached('tools.forex', 360, function () {
            // A week back, because no rate is published on some public holidays.
            $days = Http::connectTimeout(self::CONNECT_SECONDS)->timeout(self::TIMEOUT_SECONDS)
                ->get('https://www.nrb.org.np/api/forex/v1/rates', [
                    'from' => now()->subDays(6)->toDateString(),
                    'to' => now()->toDateString(),
                    'per_page' => 7,
                    'page' => 1,
                ])
                ->throw()
                ->json('data.payload');

            $latest = collect($days)->sortBy('date')->last();

            return [
                'date' => $latest['date'],
                'rates' => array_map(fn ($rate) => [
                    'code' => $rate['currency']['iso3'],
                    'name' => $rate['currency']['name'],
                    'unit' => $rate['currency']['unit'],
                    'buy' => $rate['buy'],
                    'sell' => $rate['sell'],
                ], $latest['rates']),
            ];
        });
    }

    /**
     * Current weather for each place, or an empty array.
     */
    public static function weather(): array
    {
        return static::cached('tools.weather', 30, function () {
            $places = static::forPlaces('https://api.open-meteo.com/v1/forecast', [
                'current' => 'temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code',
            ]);

            return array_map(fn ($place) => [
                'name' => $place['name'],
                'temperature' => round($place['current']['temperature_2m']),
                'feels_like' => round($place['current']['apparent_temperature']),
                'humidity' => $place['current']['relative_humidity_2m'],
                'wind' => round($place['current']['wind_speed_10m']),
                'summary' => static::weatherSummary((int) $place['current']['weather_code']),
            ], $places);
        });
    }

    /**
     * Current air quality for each place, or an empty array.
     */
    public static function airQuality(): array
    {
        return static::cached('tools.air', 60, function () {
            $places = static::forPlaces('https://air-quality-api.open-meteo.com/v1/air-quality', [
                'current' => 'us_aqi,pm2_5,pm10',
            ]);

            return array_map(fn ($place) => [
                'name' => $place['name'],
                'aqi' => (int) $place['current']['us_aqi'],
                'pm25' => round($place['current']['pm2_5'], 1),
                'pm10' => round($place['current']['pm10'], 1),
            ], $places);
        });
    }

    /**
     * The name, style and advice for an AQI reading.
     */
    public static function airBand(int $aqi): array
    {
        foreach (self::AIR_BANDS as $top => [$name, $style, $advice]) {
            if ($aqi <= $top) {
                return compact('name', 'style', 'advice');
            }
        }

        return [];
    }

    public static function airBands(): array
    {
        return self::AIR_BANDS;
    }

    /**
     * Open-Meteo answers for several places in one request, in the order they were asked.
     */
    private static function forPlaces(string $url, array $query): array
    {
        $coordinates = array_values(self::PLACES);

        $answers = Http::connectTimeout(self::CONNECT_SECONDS)->timeout(self::TIMEOUT_SECONDS)
            ->get($url, $query + [
                'latitude' => implode(',', array_column($coordinates, 0)),
                'longitude' => implode(',', array_column($coordinates, 1)),
                'timezone' => 'Asia/Kathmandu',
            ])
            ->throw()
            ->json();

        return array_map(fn ($name, $answer) => ['name' => $name] + $answer, array_keys(self::PLACES), $answers);
    }

    private static function weatherSummary(int $code): string
    {
        foreach (self::WEATHER_CODES as $top => $summary) {
            if ($code <= $top) {
                return $summary;
            }
        }

        return '';
    }

    private static function cached(string $key, int $minutes, callable $fetch): array
    {
        if (is_array($answer = Cache::get($key))) {
            return $answer;
        }

        // The lock also spaces out attempts while a source is down.
        if (Cache::add($key.'.fetching', true, now()->addMinutes(self::RETRY_MINUTES))) {
            app()->terminating(fn () => static::fetch($key, $minutes, $fetch));
        }

        return Cache::get($key.'.last', []);
    }

    private static function fetch(string $key, int $minutes, callable $fetch): void
    {
        try {
            $answer = $fetch();
            Cache::forever($key.'.last', $answer);
            Cache::put($key, $answer, now()->addMinutes($minutes));
        } catch (Throwable $e) {
            Log::warning('Tools data failed: '.$key, ['error' => $e->getMessage()]);
        }
    }
}
