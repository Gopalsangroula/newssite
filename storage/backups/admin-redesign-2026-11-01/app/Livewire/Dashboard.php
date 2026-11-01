<?php
// app/Livewire/Dashboard.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Tag;
use App\Models\Advertisement;
// use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $stats = [];
    public $recentPosts = [];
    public $recentComments = [];
    public $popularCategories = [];
    public $systemInfo = [];
    public $storageUsage = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    private function loadDashboardData()
    {
        // Main Statistics
        $this->stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'scheduled_posts' => Post::where('status', 'scheduled')->count(),
            'featured_posts' => Post::where('is_featured', true)->count(),
            'breaking_posts' => Post::where('is_breaking', true)->count(),
            'total_categories' => Category::count(),
            'total_tags' => Tag::count(),
            'total_users' => User::count(),
            'total_comments' => Comment::count(),
            'total_media' => Media::count(),
            'total_ads' => Advertisement::where('is_active', true)->count(),
            // 'total_pages' => Page::count(),
        ];

        // Recent Posts with correct relationships
        $this->recentPosts = Post::with(['category', 'createdBy'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'sub_title' => $post->sub_title,
                    'category' => $post->category->name ?? 'Uncategorized',
                    'author' => $post->createdBy->name ?? 'Unknown', // Use createdBy relationship
                    'status' => $post->status,
                    'is_featured' => $post->is_featured,
                    'is_breaking' => $post->is_breaking,
                    'cover_image' => $post->cover_image,
                    'slug' => $post->slug,
                    'created_at' => $post->created_at->diffForHumans(),
                    'published_at' => $post->published_at?->format('M j, Y'),
                ];
            })
            ->toArray();

        // Recent Comments
        $this->recentComments = Comment::with('post')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name,
                    'author_email' => $comment->author_email,
                    'body' => \Illuminate\Support\Str::limit($comment->body, 100),
                    'post_title' => $comment->post->title ?? 'Deleted Post',
                    'post_id' => $comment->post_id,
                    'created_at' => $comment->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        // // Popular Categories with post counts
        // $this->popularCategories = Category::withCount('posts')
        //     ->orderBy('posts_count', 'desc')
        //     ->take(8)
        //     ->get()
        //     ->map(function ($category) {
        //         return [
        //             'id' => $category->id,
        //             'name' => $category->name,
        //             'slug' => $category->slug,
        //             'posts_count' => $category->posts_count,
        //             'color' => $this->getCategoryColor($category->posts_count),
        //         ];
        //     })
        //     ->toArray();

        // System Information
        $this->systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => request()->server('SERVER_SOFTWARE'),
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
        ];

        // Storage Usage by file type
        $this->storageUsage = Media::select('file_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(size) as total_size'))
            ->groupBy('file_type')
            ->get()
            ->map(function ($item) {
                return [
                    'file_type' => $item->file_type,
                    'count' => $item->count,
                    'total_size' => $this->formatBytes($item->total_size),
                ];
            })
            ->toArray();
    }

    private function getCategoryColor($count)
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'secondary', 'dark', 'muted'];
        $index = min($count, 6);
        return $colors[$index] ?? 'primary';
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getPostsPerMonth()
    {
        return Post::where('created_at', '>=', now()->subMonths(12))
            ->select(DB::raw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => Carbon::create($item->year, $item->month)->format('M Y'),
                    'count' => $item->count,
                ];
            })
            ->take(6)
            ->reverse()
            ->values()
            ->toArray();
    }

    public function getTodayStats()
    {
        return [
            'today_posts' => Post::whereDate('created_at', today())->count(),
            'week_posts' => Post::where('created_at', '>=', now()->subWeek())->count(),
            'month_posts' => Post::where('created_at', '>=', now()->subMonth())->count(),
            'today_comments' => Comment::whereDate('created_at', today())->count(),
        ];
    }

    public function refreshStats()
    {
        $this->loadDashboardData();
        $this->dispatch('notify', message: 'Dashboard stats updated successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'postsPerMonth' => $this->getPostsPerMonth(),
            'todayStats' => $this->getTodayStats(),
        ]);
    }
}