<?php

namespace App\Livewire\Frontend\HomeParts;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Genealogy;
use App\Models\Page;
use App\Models\Post;
use App\Models\Visit;

class HomePosts extends Component
{
    /**
     * Categories that hold news. The others are committee profiles and reference
     * material, which have their own sections and must not lead the page.
     */
    private const NEWS_CATEGORIES = ['news', 'notice', 'activities'];

    private const TOP_STORIES = 4;

    #[Computed]
    public function categoryIds()
    {
        return Category::pluck('id', 'slug');
    }

    /**
     * The lead story and those under it: posts marked featured, then the newest news.
     */
    #[Computed]
    public function topStories()
    {
        $featured = Post::published()
            ->with('category')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(self::TOP_STORIES)
            ->get();

        return $featured->concat($this->news()->take(self::TOP_STORIES)->get())
            ->unique('id')
            ->take(self::TOP_STORIES)
            ->values();
    }

    /**
     * Posts marked breaking while they are still new, otherwise the newest news.
     */
    #[Computed]
    public function ticker()
    {
        $breaking = $this->news()
            ->where('is_breaking', true)
            ->where('published_at', '>=', now()->subDays(3))
            ->take(8)
            ->get();

        return $breaking->isNotEmpty()
            ? ['label' => 'ब्रेकिङ', 'posts' => $breaking]
            : ['label' => 'ताजा', 'posts' => $this->news()->take(8)->get()];
    }

    #[Computed]
    public function latestPosts()
    {
        return $this->news()->take(6)->get();
    }

    /**
     * The news read most in the last 30 days.
     */
    #[Computed]
    public function popularPosts()
    {
        return Visit::postStats(now()->subDays(30))
            ->published()
            ->whereNull('posts.deleted_at')
            ->whereIn('posts.category_id', $this->newsCategoryIds())
            ->orderByDesc('totals.views')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function notices()
    {
        return $this->fetchPosts('notice', 5);
    }

    #[Computed]
    public function activities()
    {
        return $this->fetchPosts('activities', 4);
    }

    #[Computed]
    public function publications()
    {
        return $this->fetchPosts('publication', 4);
    }

    #[Computed]
    public function videos()
    {
        return $this->fetchPosts('video', 4);
    }

    #[Computed]
    public function galleries()
    {
        return $this->fetchPosts('photo-gallery', 5);
    }

    /**
     * Committee members, in the same order as their own page. Titles read "सचिव - नाम" or
     * just a name; anything longer is an article filed in the same category.
     */
    #[Computed]
    public function committee()
    {
        return Post::published()
            ->where('category_id', $this->categoryIds->get('centralcomittee'))
            ->orderBy('published_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($post) {
                $title = trim(preg_replace('/\s+/u', ' ', $post->title));
                $hasRole = preg_match('/^(.{2,40}?)\s+[:\-–—]\s+(.+)$/u', $title, $parts);

                return (object) [
                    'post' => $post,
                    'role' => trim((string) $post->sub_title) ?: ($hasRole ? $parts[1] : ''),
                    'name' => $hasRole ? $parts[2] : $title,
                ];
            })
            ->filter(fn ($member) => $member->role !== '' || mb_strlen($member->name) <= 40)
            ->take(6);
    }

    #[Computed]
    public function newsGenealogy()
    {
        return Genealogy::where('pustanumber', '>=', 13)
            ->orderBy('pustanumber', 'asc')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function genealogyStats()
    {
        return [
            'people' => Genealogy::count(),
            'generations' => (int) Genealogy::max('pustanumber'),
        ];
    }

    #[Computed]
    public function pageabout()
    {
        return Page::published()->where('id', 1)->first();
    }

    #[Computed]
    public function pagemessage()
    {
        return Page::published()->where('slug', 'chairman-massage')->first();
    }

    #[Computed]
    public function ads()
    {
        return Advertisement::currentlyActive()->get()->groupBy('placement');
    }

    private function newsCategoryIds()
    {
        return $this->categoryIds->only(self::NEWS_CATEGORIES)->values();
    }

    private function news()
    {
        return Post::published()
            ->with('category')
            ->whereIn('category_id', $this->newsCategoryIds())
            ->latest('published_at');
    }

    /**
     * Fetch published posts for a category, leaving out those already at the top of the page.
     */
    private function fetchPosts(string $categorySlug, int $limit)
    {
        return Post::published()
            ->with('category')
            ->where('category_id', $this->categoryIds->get($categorySlug))
            ->whereNotIn('id', $this->topStories->pluck('id'))
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    public function render()
    {
        return view('livewire.frontend.home-parts.home-posts', [
            'topStories'     => $this->topStories,
            'ticker'         => $this->ticker,
            'latestPosts'    => $this->latestPosts,
            'popularPosts'   => $this->popularPosts,
            'notices'        => $this->notices,
            'activities'     => $this->activities,
            'publications'   => $this->publications,
            'videos'         => $this->videos,
            'galleries'      => $this->galleries,
            'committee'      => $this->committee,
            'newsGenealogy'  => $this->newsGenealogy,
            'genealogyStats' => $this->genealogyStats,
            'pageabout'      => $this->pageabout,
            'pagemessage'    => $this->pagemessage,
            'ads'            => $this->ads,
        ]);
    }
}
