<?php
// app/Livewire/Dashboard.php

namespace App\Livewire;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Genealogy;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Every logged-in role can open this page, and visitor data is personal data.
 *
 * Livewire writes public properties into the page and lets the browser call
 * public methods, so this class has neither for data: every query below is
 * protected, its result goes only to the view, and each visitor query runs
 * only for a role allowed to see the result.
 */
class Dashboard extends Component
{
    private const VISITOR_DAYS = 30;
    private const READING_NOW_MINUTES = 15;
    private const HOUR_DAYS = 7;
    private const CATEGORY_BARS = 8;
    private const SOURCE_BARS = 6;
    private const LIST_SIZE = 5;

    protected ?array $charts = null;

    public function refreshStats(): void
    {
        $this->dispatch('dashboard-charts', charts: $this->chartData());
        $this->dispatch('notify', message: 'Dashboard stats updated.', type: 'success');
    }

    public function render()
    {
        $user = Auth::user();
        $canManageContent = $user->can('access posts');
        $canSeeSeo = $user->can('access seo');
        $canSeeAds = $user->can('access advertisements');
        $canSeeComments = $user->can('access comments');
        $canSeeVisits = Visit::canViewStats($user);
        $canSeeDetails = Visit::canViewDetails($user);
        $pendingComments = $canSeeComments ? Comment::pending()->count() : null;
        $since = $this->visitorsSince();

        return view('livewire.dashboard', [
            'canManageContent' => $canManageContent,
            'canSeeSeo' => $canSeeSeo,
            'canSeeAds' => $canSeeAds,
            'canSeeComments' => $canSeeComments,
            'canSeeVisits' => $canSeeVisits,
            'canSeeDetails' => $canSeeDetails,
            'stats' => $this->contentStats(),
            'todayStats' => $this->todayStats(),
            'recentPosts' => $this->recentPosts($canManageContent),
            'storageUsage' => $this->storageUsage(),
            'categoryBars' => $this->categoryBars(),
            'charts' => $this->chartData(),
            'newsroom' => $canManageContent ? $this->newsroom() : null,
            'attention' => $canManageContent ? $this->attention($pendingComments) : [],
            'adStats' => $canSeeAds ? $this->adStats() : null,
            'pendingComments' => $pendingComments,
            'latestComments' => $canSeeComments ? $this->latestComments() : collect(),
            'topTags' => $canManageContent ? $this->topTags() : collect(),
            'visitSummary' => $canSeeVisits ? $this->visitSummary($since) : null,
            'devices' => $canSeeVisits ? $this->deviceSplit($since) : [],
            'sources' => $canSeeVisits ? $this->trafficSources($since) : [],
            'topPosts' => $canSeeVisits ? $this->topPosts($since) : collect(),
            'categoryViews' => $canSeeVisits ? $this->categoryViews($since) : [],
            'reporters' => $canSeeVisits ? $this->reporters($since) : collect(),
            'topIps' => $canSeeDetails ? $this->topIps($since) : collect(),
            'recentVisits' => $canSeeDetails ? $this->recentVisits() : collect(),
        ]);
    }

    protected function visitorsSince(): CarbonInterface
    {
        return today()->subDays(self::VISITOR_DAYS - 1);
    }

    /**
     * The data the charts draw from. The visitors series exists only for roles
     * allowed to see it, and this is also what Refresh sends to the browser.
     */
    protected function chartData(): array
    {
        $canSeeVisits = Visit::canViewStats(Auth::user());

        return $this->charts ??= [
            'months' => $this->postsPerMonth(),
            'visitors' => $canSeeVisits ? $this->visitorsPerDay($this->visitorsSince()) : null,
            'hours' => $canSeeVisits ? $this->viewsPerHour() : null,
        ];
    }

    protected function contentStats(): array
    {
        return [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'scheduled_posts' => Post::where('status', 'scheduled')->count(),
            'trashed_posts' => Post::onlyTrashed()->count(),
            'featured_posts' => Post::where('is_featured', true)->count(),
            'breaking_posts' => Post::where('is_breaking', true)->count(),
            'total_pages' => Page::count(),
            'total_categories' => Category::count(),
            'total_tags' => Tag::count(),
            'total_genealogy' => Genealogy::count(),
            'total_users' => User::count(),
            'total_comments' => Comment::count(),
            'total_media' => Media::count(),
            'total_ads' => Advertisement::where('is_active', true)->count(),
        ];
    }

    protected function todayStats(): array
    {
        return [
            'today_posts' => $this->publishedSince(today())->count(),
            'week_posts' => $this->publishedSince(today()->subDays(6))->count(),
            'month_posts' => $this->publishedSince(today()->subDays(29))->count(),
            'today_comments' => Comment::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Drafts are only for roles that work on posts.
     */
    protected function recentPosts(bool $canManageContent): Collection
    {
        return Post::with(['category', 'users', 'createdBy'])
            ->when(! $canManageContent, fn ($query) => $query->published())
            ->latest()
            ->take(6)
            ->get();
    }

    protected function publishedSince(CarbonInterface $from): Builder
    {
        return Post::published()->whereRaw('COALESCE(published_at, created_at) >= ?', [$from]);
    }

    /**
     * The posts this user works on: all of them, or for an editor their own,
     * the same rule as the Posts page.
     */
    protected function workablePosts(): Builder
    {
        $user = Auth::user();

        return Post::query()->when($user->hasRole('editor'), fn ($query) => $query->where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('users', fn ($authors) => $authors->where('users.id', $user->id));
        }));
    }

    /**
     * What is live on the site now and what is waiting to go out.
     */
    protected function newsroom(): array
    {
        $publishedToday = $this->publishedSince(today())->count();
        $scheduled = $this->workablePosts()->scheduled();

        return [
            'published_today' => $publishedToday,
            'published_yesterday' => $this->publishedSince(today()->subDay())->count() - $publishedToday,
            'published_total' => Post::published()->count(),
            'drafts' => $this->workablePosts()->draft()->count(),
            'scheduled' => (clone $scheduled)->count(),
            'breaking' => Post::published()->where('is_breaking', true)->count(),
            'featured' => Post::published()->where('is_featured', true)->count(),
            'headlines' => Post::published()
                ->with('category')
                ->where(fn ($query) => $query->where('is_breaking', true)->orWhere('is_featured', true))
                ->orderByDesc('is_breaking')
                ->latest('published_at')
                ->take(self::LIST_SIZE)
                ->get(),
            'upcoming' => (clone $scheduled)->with('category')->orderBy('published_at')->take(self::LIST_SIZE)->get(),
            'draft_list' => $this->workablePosts()->draft()->with('category')->latest('updated_at')->take(self::LIST_SIZE)->get(),
        ];
    }

    /**
     * Comments to approve, for roles that can, and live posts that are missing
     * something a news story should have.
     */
    protected function attention(?int $pendingComments): array
    {
        $live = $this->workablePosts()->published();
        $missing = fn (string $column) => (clone $live)
            ->where(fn ($query) => $query->whereNull($column)->orWhere($column, ''))
            ->count();

        $comments = $pendingComments === null ? [] : ['Comments waiting' => $pendingComments];

        return $comments + [
            'No cover image' => $missing('cover_image'),
            'No sub title' => $missing('sub_title'),
            'No category' => (clone $live)->whereDoesntHave('category')->count(),
            'No tags' => (clone $live)->whereDoesntHave('tags')->count(),
            'No meta description' => $missing('seo_description'),
            'No focus keyword' => $missing('focus_keyword'),
            'Hidden from search' => (clone $live)->where('noindex', true)->count(),
        ];
    }

    protected function adStats(): array
    {
        return [
            'Running now' => Advertisement::currentlyActive()->count(),
            'Ending within 7 days' => Advertisement::currentlyActive()->whereBetween('ends_at', [now(), now()->addDays(7)])->count(),
            'Starting later' => Advertisement::where('is_active', true)->where('starts_at', '>', now())->count(),
            'Ended' => Advertisement::where('is_active', true)->where('ends_at', '<', now())->count(),
            'Switched off' => Advertisement::where('is_active', false)->count(),
        ];
    }

    protected function latestComments(): Collection
    {
        return Comment::with('post:id,title')->latest()->take(self::LIST_SIZE)->get();
    }

    protected function topTags(): Collection
    {
        return Tag::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->take(self::LIST_SIZE)
            ->get();
    }

    protected function storageUsage(): array
    {
        return Media::select('file_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(size) as total_size'))
            ->groupBy('file_type')
            ->get()
            ->map(fn ($item) => [
                'file_type' => $item->file_type,
                'count' => $item->count,
                'total_size' => $this->formatBytes($item->total_size),
            ])
            ->toArray();
    }

    protected function formatBytes($bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((float) $bytes, 0);
        $pow = (int) min(floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision).' '.$units[$pow];
    }

    protected function categoryBars(): array
    {
        return $this->barsPerCategory(
            Post::published()
                ->selectRaw('category_id, COUNT(*) AS total')
                ->groupBy('category_id')
                ->orderByDesc('total')
                ->toBase()
                ->get()
        );
    }

    /**
     * Which sections people read, trashed posts included so that the total matches the visits.
     */
    protected function categoryViews(CarbonInterface $since): array
    {
        return $this->barsPerCategory(
            Post::withTrashed()
                ->join('visits', 'visits.post_id', '=', 'posts.id')
                ->where('visits.created_at', '>=', $since)
                ->selectRaw('posts.category_id, COUNT(*) AS total')
                ->groupBy('posts.category_id')
                ->orderByDesc('total')
                ->toBase()
                ->get()
        );
    }

    /**
     * Totals per category, largest first, ready for the bar list.
     */
    protected function barsPerCategory(Collection $rows): array
    {
        $names = Category::withTrashed()->whereIn('id', $rows->pluck('category_id'))->pluck('name', 'id');

        return $this->bars(
            $rows->map(fn ($row) => [
                'label' => $names[$row->category_id] ?? 'Uncategorized',
                'value' => (int) $row->total,
            ]),
            self::CATEGORY_BARS,
            'categories'
        );
    }

    /**
     * Beyond the first few the rows fold into one, because the list stops being readable.
     */
    protected function bars(Collection $rows, int $limit, string $restName): array
    {
        $bars = $rows->take($limit)->values();
        $rest = $rows->slice($limit);

        if ($rest->isNotEmpty()) {
            $bars->push([
                'label' => 'Other ('.$rest->count().' '.$restName.')',
                'value' => (int) $rest->sum('value'),
            ]);
        }

        $largest = max($bars->max('value') ?? 0, 1);

        return $bars->map(fn ($bar) => $bar + ['percent' => round($bar['value'] / $largest * 100, 1)])->all();
    }

    /**
     * Published posts per month for the last 12 months, empty months included.
     */
    protected function postsPerMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        $totals = Post::published()
            ->whereRaw('COALESCE(published_at, created_at) >= ?', [$start])
            ->selectRaw("DATE_FORMAT(COALESCE(published_at, created_at), '%Y-%m') AS month, COUNT(*) AS total")
            ->groupBy('month')
            ->toBase()
            ->pluck('total', 'month');

        $series = ['labels' => [], 'titles' => [], 'posts' => []];

        for ($offset = 0; $offset < 12; $offset++) {
            $month = $start->copy()->addMonths($offset);

            $series['labels'][] = $month->format("M 'y");
            $series['titles'][] = $month->format('F Y');
            $series['posts'][] = (int) ($totals[$month->format('Y-m')] ?? 0);
        }

        return $series;
    }

    /**
     * Unique visitors and page views per day, empty days included.
     */
    protected function visitorsPerDay(CarbonInterface $since): array
    {
        $rows = Visit::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS views, COUNT(DISTINCT visitor_key) AS visitors')
            ->groupBy('day')
            ->toBase()
            ->get()
            ->keyBy('day');

        $series = ['labels' => [], 'titles' => [], 'visitors' => [], 'views' => []];

        foreach (CarbonPeriod::create($since, today()) as $day) {
            $key = $day->toDateString();

            $series['labels'][] = $day->format('M j');
            $series['titles'][] = $day->format('D, M j, Y');
            $series['visitors'][] = (int) ($rows[$key]->visitors ?? 0);
            $series['views'][] = (int) ($rows[$key]->views ?? 0);
        }

        return $series;
    }

    /**
     * Page views by hour of the day, to show when people read and when a story
     * goes out to the most readers.
     */
    protected function viewsPerHour(): array
    {
        $totals = Visit::where('created_at', '>=', today()->subDays(self::HOUR_DAYS - 1))
            ->selectRaw('HOUR(created_at) AS hour, COUNT(*) AS views')
            ->groupBy('hour')
            ->toBase()
            ->pluck('views', 'hour');

        $series = ['labels' => [], 'titles' => [], 'views' => []];

        foreach (range(0, 23) as $hour) {
            $start = today()->setHour($hour);

            $series['labels'][] = $start->format('ga');
            $series['titles'][] = $start->format('g A').' to '.$start->copy()->addHour()->format('g A');
            $series['views'][] = (int) ($totals[$hour] ?? 0);
        }

        return $series;
    }

    protected function visitSummary(CarbonInterface $since): array
    {
        $today = Visit::where('created_at', '>=', today());

        return [
            'reading_now' => Visit::where('created_at', '>=', now()->subMinutes(self::READING_NOW_MINUTES))->distinct()->count('visitor_key'),
            'visitors_today' => (clone $today)->distinct()->count('visitor_key'),
            'views_today' => (clone $today)->count(),
            'visitors_period' => Visit::where('created_at', '>=', $since)->distinct()->count('visitor_key'),
        ];
    }

    /**
     * How readers reached the site. Clicks between pages of the site are left out.
     */
    protected function trafficSources(CarbonInterface $since): array
    {
        $sources = Visit::where('created_at', '>=', $since)
            ->selectRaw('referrer, COUNT(*) AS total')
            ->groupBy('referrer')
            ->toBase()
            ->get()
            ->groupBy(fn ($row) => Visit::sourceOf($row->referrer))
            ->forget('')
            ->map(fn ($rows, $name) => ['label' => $name, 'value' => (int) $rows->sum('total')])
            ->sortByDesc('value')
            ->values();

        return $this->bars($sources, self::SOURCE_BARS, 'sites');
    }

    /**
     * Stories and readers per reporter. A post belongs to its first listed author,
     * otherwise to whoever created it, as on the public site.
     */
    protected function reporters(CarbonInterface $since): Collection
    {
        $views = Visit::where('created_at', '>=', $since)
            ->whereNotNull('post_id')
            ->selectRaw('post_id, COUNT(*) AS views')
            ->groupBy('post_id');

        $rows = Post::published()
            ->leftJoinSub($views, 'totals', 'totals.post_id', '=', 'posts.id')
            ->selectRaw(
                'COALESCE((SELECT MIN(post_user.user_id) FROM post_user WHERE post_user.post_id = posts.id), posts.created_by) AS author_id,
                COUNT(*) AS posts,
                SUM(COALESCE(published_at, created_at) >= ?) AS recent_posts,
                COALESCE(SUM(totals.views), 0) AS views',
                [$since]
            )
            ->groupBy('author_id')
            ->orderByDesc('views')
            ->orderByDesc('posts')
            ->limit(self::LIST_SIZE)
            ->toBase()
            ->get();

        $names = User::whereIn('id', $rows->pluck('author_id'))->pluck('name', 'id');

        return $rows->map(fn ($row) => (object) [
            'name' => $names[$row->author_id] ?? 'Deleted User',
            'posts' => (int) $row->posts,
            'recent_posts' => (int) $row->recent_posts,
            'views' => (int) $row->views,
        ]);
    }

    /**
     * Unique visitors per device. Each device keeps its place and colour even
     * when another device has no visitors.
     */
    protected function deviceSplit(CarbonInterface $since): array
    {
        $counts = Visit::where('created_at', '>=', $since)
            ->selectRaw('device, COUNT(DISTINCT visitor_key) AS total')
            ->groupBy('device')
            ->toBase()
            ->pluck('total', 'device');

        $sum = max((int) $counts->sum(), 1);

        return collect(['desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'])
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => (int) ($counts[$key] ?? 0),
                'share' => round(($counts[$key] ?? 0) / $sum * 100),
            ])
            ->values()
            ->all();
    }

    protected function topPosts(CarbonInterface $since): Collection
    {
        return Visit::postStats($since)
            ->with('category')
            ->orderByDesc('totals.views')
            ->orderBy('posts.id')
            ->limit(10)
            ->get();
    }

    protected function recentVisits(): Collection
    {
        return Visit::with('post:id,title')->latest('id')->limit(15)->get();
    }

    protected function topIps(CarbonInterface $since): Collection
    {
        return Visit::where('created_at', '>=', $since)
            ->whereNotNull('ip_address')
            ->selectRaw('ip_address, COUNT(*) AS visits, MAX(created_at) AS last_seen')
            ->groupBy('ip_address')
            ->orderByDesc('visits')
            ->limit(10)
            ->withCasts(['last_seen' => 'datetime'])
            ->get();
    }
}
