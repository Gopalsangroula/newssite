<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    /**
     * Visits are written once and never updated.
     */
    const UPDATED_AT = null;

    /**
     * Sites that send readers, matched against the referring host.
     */
    private const SOURCES = [
        'Facebook' => '/(^|\.)(facebook\.com|fb\.com|fb\.me|messenger\.com)$/',
        'Google' => '/(^|\.)google\.[a-z.]+$/',
        'YouTube' => '/(^|\.)(youtube\.com|youtu\.be)$/',
        'X (Twitter)' => '/(^|\.)(x\.com|twitter\.com|t\.co)$/',
        'Instagram' => '/(^|\.)instagram\.com$/',
        'TikTok' => '/(^|\.)tiktok\.com$/',
        'Bing' => '/(^|\.)bing\.com$/',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'path',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'referrer',
        'visitor_key',
    ];

    /**
     * Get the post that was viewed, if the visit was to a post.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Whether the user may see aggregate counts: totals, charts and per-post numbers.
     */
    public static function canViewStats(?User $user): bool
    {
        return $user !== null && $user->can('access analytics');
    }

    /**
     * Whether the user may see personal data: IP addresses and individual visits.
     */
    public static function canViewDetails(?User $user): bool
    {
        return $user !== null && $user->can('view visitor details');
    }

    /**
     * Posts with their views, unique visitors and latest view since the given time,
     * and how many of those views came today and in the last 7 days.
     * Trashed posts are included so that their traffic does not silently disappear.
     */
    public static function postStats(CarbonInterface $since): Builder
    {
        $totals = static::query()
            ->selectRaw(
                'post_id, COUNT(*) AS views, COUNT(DISTINCT visitor_key) AS visitors, MAX(created_at) AS last_viewed_at,
                SUM(created_at >= ?) AS views_today, SUM(created_at >= ?) AS views_week',
                [today(), today()->subDays(6)]
            )
            ->whereNotNull('post_id')
            ->where('created_at', '>=', $since)
            ->groupBy('post_id');

        return Post::withTrashed()
            ->joinSub($totals, 'totals', 'totals.post_id', '=', 'posts.id')
            ->select(
                'posts.id', 'posts.title', 'posts.slug', 'posts.category_id', 'posts.deleted_at',
                'totals.views', 'totals.visitors', 'totals.last_viewed_at', 'totals.views_today', 'totals.views_week'
            )
            ->withCasts(['last_viewed_at' => 'datetime']);
    }

    /**
     * The site a visit came from: a known name, the host of any other site, or
     * Direct when there was no referrer. Empty for a click within this site.
     */
    public static function sourceOf(?string $referrer): string
    {
        if (blank($referrer)) {
            return 'Direct';
        }

        $withoutWww = fn (string $host) => preg_replace('/^www\./i', '', $host);
        $host = strtolower($withoutWww(parse_url($referrer, PHP_URL_HOST) ?: $referrer));

        if ($host === strtolower($withoutWww(request()->getHost()))) {
            return '';
        }

        foreach (self::SOURCES as $name => $pattern) {
            if (preg_match($pattern, $host) === 1) {
                return $name;
            }
        }

        return $host;
    }

    /**
     * Where the visitor came from, in a form short enough for a table cell.
     */
    public function getReferrerLabelAttribute(): string
    {
        if (! $this->referrer) {
            return 'Direct';
        }

        $host = parse_url($this->referrer, PHP_URL_HOST) ?: $this->referrer;

        return $host === request()->getHost() ? 'Internal' : $host;
    }
}
