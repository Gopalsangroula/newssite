<?php
// app/Livewire/Dashboard.php

namespace App\Livewire;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Every logged-in role can open this page, so each query runs only for a role allowed
 * to see the result, and results go to the view rather than into public properties.
 */
class Dashboard extends Component
{
    private const RECENT_DAYS = 30;
    private const CATEGORY_BARS = 8;
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
        $pendingComments = $canSeeComments ? Comment::pending()->count() : null;

        return view('livewire.dashboard', [
            'canManageContent' => $canManageContent,
            'canSeeSeo' => $canSeeSeo,
            'canSeeAds' => $canSeeAds,
            'canSeeComments' => $canSeeComments,
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
            'topPosts' => $canManageContent ? $this->topPosts() : collect(),
            'reporters' => $canManageContent ? $this->reporters() : collect(),
        ]);
    }

    /**
     * The data the charts draw from. This is also what Refresh sends to the browser.
     */
    protected function chartData(): array
    {
        return $this->charts ??= [
            'months' => $this->postsPerMonth(),
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

    /**
     * Published posts per category, largest first, ready for the bar list.
     */
    protected function categoryBars(): array
    {
        $rows = Post::published()
            ->selectRaw('category_id, COUNT(*) AS total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->toBase()
            ->get();

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
     * Stories and views per reporter. A post belongs to its first listed author,
     * otherwise to whoever created it, as on the public site.
     */
    protected function reporters(): Collection
    {
        $rows = Post::published()
            ->selectRaw(
                'COALESCE((SELECT MIN(post_user.user_id) FROM post_user WHERE post_user.post_id = posts.id), posts.created_by) AS author_id,
                COUNT(*) AS posts,
                SUM(COALESCE(published_at, created_at) >= ?) AS recent_posts,
                SUM(views) AS views',
                [today()->subDays(self::RECENT_DAYS - 1)]
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

    protected function topPosts(): Collection
    {
        return Post::with('category')
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }
}
