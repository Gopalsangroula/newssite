<?php

namespace App\Support;

/**
 * A YouTube or Facebook video address, as typed into the post editor.
 */
class VideoLink
{
    private const YOUTUBE_HOSTS = ['youtube.com', 'youtube-nocookie.com', 'youtu.be'];

    private const FACEBOOK_HOSTS = ['facebook.com', 'fb.watch'];

    /**
     * The editor's check: an empty box, or a video on one of the two sites.
     */
    public static function rule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (filled($value) && ! static::parse(is_string($value) ? $value : null)) {
                $fail('Use the link of a YouTube or Facebook video.');
            }
        };
    }

    /**
     * The video an address points to, or null for anything else. A page only ever gets the
     * YouTube id, or the Facebook address put back together from parts that were checked.
     *
     * @return array{site: string, id: string, url: string}|null
     */
    public static function parse(?string $address): ?array
    {
        $address = trim((string) $address);

        // Addresses are often copied without the https:// in front.
        if ($address !== '' && ! preg_match('~^[a-z][a-z0-9+.-]*://~i', $address)) {
            $address = 'https://'.$address;
        }

        $parts = parse_url($address);

        if (! is_array($parts) || empty($parts['host']) || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }

        $host = preg_replace('/^(?:www|m|mobile|web)\./', '', strtolower($parts['host']));
        $path = $parts['path'] ?? '/';
        parse_str($parts['query'] ?? '', $query);

        if (in_array($host, self::YOUTUBE_HOSTS, true)) {
            $id = match (true) {
                $host === 'youtu.be' => explode('/', ltrim($path, '/'))[0],
                rtrim($path, '/') === '/watch' => $query['v'] ?? '',
                default => preg_match('~^/(?:embed|shorts|live|v)/([^/]+)~', $path, $match) ? $match[1] : '',
            };

            return is_string($id) && preg_match('/^[\w-]{11}$/', $id)
                ? ['site' => 'youtube', 'id' => $id, 'url' => 'https://www.youtube.com/watch?v='.$id]
                : null;
        }

        if (in_array($host, self::FACEBOOK_HOSTS, true) && $path !== '/' && preg_match('~^/[\w\-./]+$~', $path)) {
            $number = is_string($query['v'] ?? null) && ctype_digit($query['v']) ? $query['v'] : null;

            return [
                'site' => 'facebook',
                'id' => $number ?? (preg_match('~\d{6,}~', $path, $match) ? $match[0] : $path),
                'url' => 'https://'.($host === 'fb.watch' ? 'fb.watch' : 'www.facebook.com').$path.($number ? '?v='.$number : ''),
            ];
        }

        return null;
    }
}
