<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Smaller copies of uploaded pictures, because a 10 MB photo costs the same to send whether it
 * fills the screen or a small card. Originals are never touched. Until a copy exists, or
 * on a server without GD, pages simply use the original.
 *
 * Copies are made by re-drawing the picture, so nothing hidden in an upload survives,
 * and neither does location data from a phone camera.
 */
class Thumb
{
    /**
     * Widths for pages, in pixels. WebP, because it is about a third smaller than JPEG.
     */
    public const WIDTHS = [320, 640, 1024];

    /**
     * For Facebook and other link previews, which need a JPEG they can read quickly.
     */
    private const SHARE_WIDTH = 1200;

    private const CONTENT_WIDTH = 1024;
    private const FOLDER = 'thumbs';
    private const WEBP_QUALITY = 78;
    private const JPEG_QUALITY = 82;

    /**
     * Pixels are held uncompressed while resizing, at about this many bytes each.
     */
    private const BYTES_PER_PIXEL = 5;
    private const SPARE_MEMORY_BYTES = 32000000;

    private const LOCAL_STORAGE_URL = '~^(?:https?://[^/"\']+)?/storage/(?!thumbs/)([^"\'?#]+\.(?:jpe?g|png|webp))$~i';

    /**
     * The address of the copy closest to this width, or of the original.
     */
    public static function url(?string $path, int $width): string
    {
        $copy = self::copyPath((string) $path, $width, 'webp');

        return Storage::url(self::exists($copy) ? $copy : (string) $path);
    }

    /**
     * The address for link previews, or null when there is no copy.
     */
    public static function shareUrl(?string $path): ?string
    {
        $copy = self::copyPath((string) $path, self::SHARE_WIDTH, 'jpg');

        return self::exists($copy) ? Storage::url($copy) : null;
    }

    /**
     * Story HTML with its local pictures swapped for copies, loading only as the reader reaches them.
     */
    public static function inContent(?string $html): string
    {
        return (string) preg_replace_callback('/<img\b[^>]*>/i', function ($match) {
            $tag = preg_replace_callback('/\bsrc=(["\'])(.*?)\1/i', function ($source) {
                if (! preg_match(self::LOCAL_STORAGE_URL, $source[2], $local)) {
                    return $source[0];
                }

                $path = rawurldecode($local[1]);
                $copy = self::copyPath($path, self::CONTENT_WIDTH, 'webp');

                return self::exists($copy) ? 'src='.$source[1].Storage::url($copy).$source[1] : $source[0];
            }, $match[0]);

            return stripos($tag, ' loading=') === false
                ? preg_replace('/^<img\b/i', '<img loading="lazy" decoding="async"', $tag)
                : $tag;
        }, (string) $html);
    }

    /**
     * Makes copies for a saved post or page once the editor's response has gone out.
     */
    public static function makeAfterResponse(?string $cover, ?string $html): void
    {
        $paths = array_filter([$cover, ...self::pathsIn($html)]);

        if ($paths !== []) {
            app()->terminating(function () use ($paths) {
                foreach ($paths as $path) {
                    self::make($path);
                }
            });
        }
    }

    /**
     * Makes the missing copies of one picture, and returns how many were written.
     */
    public static function make(string $path): int
    {
        $source = storage_path('app/public/'.$path);

        if (! function_exists('imagewebp') || Str::contains($path, '..') || ! is_file($source)) {
            return 0;
        }

        // GIFs are left alone so that they keep moving.
        [$width, $height, $type] = @getimagesize($source) ?: [0, 0, 0];

        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return 0;
        }

        // Link previews are for photos. A transparent logo would turn black as a JPEG.
        $sizes = $type === IMAGETYPE_JPEG ? [...self::WIDTHS, self::SHARE_WIDTH] : self::WIDTHS;
        $missing = array_filter($sizes, fn ($size) => $size < $width
            && ! self::exists(self::copyPath($path, $size, $size === self::SHARE_WIDTH ? 'jpg' : 'webp')));

        if ($missing === [] || ! self::fitsInMemory($width, $height)) {
            return 0;
        }

        try {
            $image = self::open($source, $type);

            if ($image === null) {
                return 0;
            }

            foreach ($missing as $size) {
                $resized = imagescale($image, $size);
                imagesavealpha($resized, true);
                self::write($resized, $path, $size);
                imagedestroy($resized);
            }

            imagedestroy($image);

            return count($missing);
        } catch (Throwable $e) {
            Log::warning('Thumbnail failed: '.$path, ['error' => $e->getMessage()]);

            return 0;
        }
    }

    private static function pathsIn(?string $html): array
    {
        preg_match_all('/<img\b[^>]*\bsrc=(["\'])(.*?)\1/i', (string) $html, $sources);

        return array_values(array_filter(array_map(
            fn ($url) => preg_match(self::LOCAL_STORAGE_URL, $url, $local) ? rawurldecode($local[1]) : null,
            $sources[2]
        )));
    }

    private static function copyPath(string $path, int $width, string $format): string
    {
        return self::FOLDER.'/'.$width.'/'.$path.'.'.$format;
    }

    private static function exists(string $copy): bool
    {
        return is_file(storage_path('app/public/'.$copy));
    }

    private static function fitsInMemory(int $width, int $height): bool
    {
        $limit = ini_parse_quantity((string) ini_get('memory_limit'));

        return $limit <= 0 || memory_get_usage(true) + $width * $height * self::BYTES_PER_PIXEL + self::SPARE_MEMORY_BYTES < $limit;
    }

    private static function open(string $source, int $type)
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        };

        if ($image === false) {
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $type === IMAGETYPE_JPEG ? self::turnUpright($image, $source) : $image;
    }

    /**
     * Phones store the camera's angle as a note beside the picture, which drawing it anew would lose.
     */
    private static function turnUpright($image, string $source)
    {
        $angle = match (function_exists('exif_read_data') ? (@exif_read_data($source)['Orientation'] ?? 1) : 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $turned = imagerotate($image, $angle, 0);
        imagedestroy($image);

        return $turned;
    }

    private static function write($image, string $path, int $size): void
    {
        $isShare = $size === self::SHARE_WIDTH;
        $file = storage_path('app/public/'.self::copyPath($path, $size, $isShare ? 'jpg' : 'webp'));
        $temporary = $file.'.'.getmypid().'.tmp';

        if (! is_dir(dirname($file))) {
            @mkdir(dirname($file), 0775, true);
        }

        $isShare ? imagejpeg($image, $temporary, self::JPEG_QUALITY) : imagewebp($image, $temporary, self::WEBP_QUALITY);

        rename($temporary, $file);
    }
}
