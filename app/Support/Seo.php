<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Seo
{
    public const TITLE_MIN = 30;
    public const TITLE_MAX = 60;
    public const DESCRIPTION_MIN = 70;
    public const DESCRIPTION_MAX = 160;
    public const CONTENT_MIN_WORDS = 150;

    /**
     * Pages whose content depends on the reader, so they should not appear in search results.
     */
    private const NOINDEX_ROUTES = ['frontend.search', 'frontend.saved'];

    /**
     * Site-wide values used until they are changed on the SEO Settings page.
     */
    public static function defaults(): array
    {
        return [
            'site_name' => 'Kettakum Libang Pangpat Sayang',
            'title_suffix' => 'Libang',
            'home_title' => 'केत्तकुम लिबाङ पाङ्पत सयङ | Kettakum Libang Pangpat Sayang - Limbu News & Culture',
            'description' => 'केत्तकुम लिबाङ पाङ्पत सयङ लिम्बू (याक्थुङ) समुदायको समाचार, संस्कृति र इतिहास प्रकाशित गर्ने सामाजिक संस्था हो। Official news and culture portal of the Libang Limbu community.',
            'keywords' => 'केत्तकुम लिबाङ पाङ्पत सयङ, Kettakum Libang Pangpat Sayang, Libang, Libang news, Limbu news, Limbu culture, Yakthung Limbu, Limbuwan',
            'share_image' => 'img/logo-libang.png',
            'facebook_app_id' => '401514826619301',
            'facebook_page_id' => '737640319702735',
            'twitter_handle' => '@appharu',
            'google_verification' => '',
            'bing_verification' => '',
        ];
    }

    public static function site(): array
    {
        return array_merge(static::defaults(), SiteSetting::values());
    }

    /**
     * Everything the public layouts print in <head> for the current page.
     * Pass a Post or Page, or a plain title and description for other pages.
     */
    public static function meta($model = null, ?string $title = null, ?string $description = null): array
    {
        $site = static::site();
        $content = $model instanceof Post || $model instanceof Page ? $model : null;
        $post = $content instanceof Post ? $content : null;

        $meta = [
            'site' => $site,
            'title' => $content ? static::pageTitle($content, $site) : ($title ?: $site['home_title']),
            'social_title' => $content ? ($content->seo_title ?: $content->title) : ($title ?: $site['home_title']),
            'description' => Str::limit(static::plainText(
                $content?->seo_description ?: $post?->excerpt ?: $post?->sub_title ?: $content?->content ?: $description ?: $site['description']
            ), static::DESCRIPTION_MAX),
            'image' => static::imageUrl($content, $site),
            'url' => url()->current(),
            'type' => $post ? 'article' : 'website',
            'robots' => $content?->noindex || request()->routeIs(...self::NOINDEX_ROUTES) ? 'noindex, follow' : 'index, follow, max-image-preview:large',
            'keywords' => $content ? $content->focus_keyword : $site['keywords'],
            'author' => $post?->author?->name ?? $site['site_name'],
            'post' => $post,
            'published_at' => $post ? ($post->published_at ?? $post->created_at) : null,
        ];

        // Posts from before the move to Nepal time keep an updated_at that reads 5:45 early.
        $meta['modified_at'] = $post ? $post->updated_at->max($meta['published_at']) : null;
        $meta['image_size'] = static::imageSize($meta['image']);
        $meta['schema'] = $post ? static::articleSchema($meta) : null;

        return $meta;
    }

    /**
     * The <title> shown in search results: the custom SEO title as written,
     * otherwise the content title followed by the site suffix.
     */
    public static function pageTitle($content, array $site): string
    {
        return $content->seo_title ?: trim($content->title.' | '.$site['title_suffix']);
    }

    /**
     * The checks behind the score on the SEO page. resources/views/partials/editor-seo.blade.php
     * runs the same checks live in the browser while a post is being written.
     */
    public static function audit($content): array
    {
        $site = static::site();
        $keyword = Str::lower(trim((string) $content->focus_keyword));
        $title = static::pageTitle($content, $site);
        $description = trim((string) $content->seo_description);
        $body = static::plainText($content->content);
        $firstParagraph = preg_match('/<p[^>]*>(.*?)<\/p>/su', (string) $content->content, $match)
            ? static::plainText($match[1])
            : $body;
        $hasKeyword = fn (string $text) => $keyword !== '' && Str::contains(Str::lower($text), $keyword);
        $titleLength = mb_strlen($title);
        $descriptionLength = mb_strlen($description);

        $checks = [
            'title' => $titleLength >= static::TITLE_MIN && $titleLength <= static::TITLE_MAX,
            'description' => $descriptionLength >= static::DESCRIPTION_MIN && $descriptionLength <= static::DESCRIPTION_MAX,
            'image' => filled($content->og_image) || filled($content->cover_image),
            'keyword' => $keyword !== '',
            'keyword_in_title' => $hasKeyword($title),
            'keyword_in_description' => $hasKeyword($description),
            'keyword_in_intro' => $hasKeyword($firstParagraph),
            'length' => count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY)) >= static::CONTENT_MIN_WORDS,
        ];

        return [
            'checks' => $checks,
            'score' => (int) round(count(array_filter($checks)) / count($checks) * 100),
        ];
    }

    /**
     * Text with tags and block comments removed and entities such as &ldquo; decoded,
     * so it reads correctly in search results and link previews.
     */
    public static function plainText(?string $html): string
    {
        $text = preg_replace('/<br\s*\/?>/i', ' ', (string) $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public static function absoluteUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : url($path);
    }

    protected static function imageUrl($content, array $site): string
    {
        $path = $content?->og_image
            ?: ($content?->cover_image ? (Thumb::shareUrl($content->cover_image) ?? Storage::url($content->cover_image)) : null)
            ?: $site['share_image']
            ?: static::defaults()['share_image'];

        return static::absoluteUrl($path);
    }

    /**
     * The real width and height of a local image, so Facebook can lay out the
     * preview on the first share. Empty for files stored elsewhere.
     */
    protected static function imageSize(string $url): array
    {
        $path = urldecode((string) parse_url($url, PHP_URL_PATH));
        $file = Str::startsWith($path, '/storage/')
            ? storage_path('app/public/'.Str::after($path, '/storage/'))
            : public_path(ltrim($path, '/'));

        $size = is_file($file) ? @getimagesize($file) : false;

        return $size ? ['width' => $size[0], 'height' => $size[1]] : [];
    }

    protected static function articleSchema(array $meta): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => $meta['url'],
            'headline' => Str::limit($meta['social_title'], 110, ''),
            'description' => $meta['description'],
            'image' => [$meta['image']],
            'datePublished' => $meta['published_at']->toIso8601String(),
            'dateModified' => $meta['modified_at']->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $meta['author']],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $meta['site']['site_name'],
                'logo' => ['@type' => 'ImageObject', 'url' => asset('img/logo-libang.png')],
            ],
        ];
    }
}
