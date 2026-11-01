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
     * Categories that hold news, along with everything under them. The others are
     * committee profiles and reference material, which must not lead the page.
     */
    private const NEWS_CATEGORIES = ['news', 'notice', 'activities'];

    private const MAX_LEAD_STORIES = 2;

    /**
     * Sections beside the right-hand column, in page order. A section with no posts is left out.
     */
    private const MAIN_SECTIONS = [
        'activities' => ['icon' => 'calendar', 'layout' => 'cards', 'limit' => 6],
        'samaj' => ['icon' => 'users', 'layout' => 'cards', 'limit' => 6],
    ];

    /**
     * Full-width sections lower down the page.
     */
    private const WIDE_SECTIONS = [
        'artha-bikas' => ['icon' => 'credit-card', 'layout' => 'feature', 'limit' => 4],
        'baatchit' => ['icon' => 'microphone', 'layout' => 'feature', 'limit' => 4],
        'khelkud' => ['icon' => 'lifesaver', 'layout' => 'cards', 'limit' => 3],
        'kala' => ['icon' => 'paint-bucket', 'layout' => 'cards', 'limit' => 3],
    ];

    #[Computed]
    public function categories()
    {
        return Category::get(['id', 'name', 'slug', 'parent_id'])->keyBy('slug');
    }

    /**
     * The stories across the top of the page: posts marked featured, or the newest news.
     */
    #[Computed]
    public function leads()
    {
        $featured = Post::published()
            ->with('category')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(self::MAX_LEAD_STORIES)
            ->get();

        return $featured->isNotEmpty() ? $featured : $this->news()->take(1)->get();
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
        return $this->news()->take(8)->get();
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
            ->take(5)
            ->get();
    }

    /**
     * The section under the lead stories. Until the current affairs category
     * has posts, the newest news fills its place.
     */
    #[Computed]
    public function firstSection()
    {
        $current = $this->sections(['samasamayik' => ['icon' => 'bolt', 'layout' => 'feature', 'limit' => 5]])->first();

        if ($current) {
            return $current;
        }

        $posts = $this->news()->whereNotIn('id', $this->leads->pluck('id'))->take(5)->get();

        return $posts->isEmpty() ? null : (object) [
            'title' => 'पछिल्लो अपडेट',
            'url' => route('frontend.category.show', 'news'),
            'icon' => 'rss',
            'layout' => 'feature',
            'posts' => $posts,
        ];
    }

    #[Computed]
    public function mainSections()
    {
        return $this->sections(self::MAIN_SECTIONS);
    }

    #[Computed]
    public function wideSections()
    {
        return $this->sections(self::WIDE_SECTIONS);
    }

    #[Computed]
    public function special()
    {
        return $this->fetchPosts('bishesh', 7);
    }

    #[Computed]
    public function notices()
    {
        return $this->fetchPosts('notice', 5);
    }

    #[Computed]
    public function opinions()
    {
        return $this->fetchPosts('drishtikon', 4)->load(['users', 'createdBy']);
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
            ->where('category_id', $this->categories->get('centralcomittee')?->id)
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

    /**
     * The listed sections with their posts, without any that have none.
     */
    private function sections(array $sections)
    {
        return collect($sections)
            ->map(fn ($section, $slug) => (object) ([
                'title' => $this->categories->get($slug)?->name,
                'url' => route('frontend.category.show', $slug),
                'posts' => $this->fetchPosts($slug, $section['limit']),
            ] + $section))
            ->filter(fn ($section) => $section->posts->isNotEmpty());
    }

    /**
     * A category and the ones under it.
     */
    private function familyIds(string $slug)
    {
        $category = $this->categories->get($slug);

        return $category
            ? $this->categories->where('parent_id', $category->id)->pluck('id')->push($category->id)
            : collect();
    }

    private function newsCategoryIds()
    {
        return collect(self::NEWS_CATEGORIES)->flatMap(fn ($slug) => $this->familyIds($slug));
    }

    private function news()
    {
        return Post::published()
            ->with('category')
            ->inCategories($this->newsCategoryIds())
            ->latest('published_at');
    }

    /**
     * Fetch published posts for a category, leaving out those already leading the page.
     */
    private function fetchPosts(string $categorySlug, int $limit)
    {
        return Post::published()
            ->with('category')
            ->inCategories($this->familyIds($categorySlug))
            ->whereNotIn('id', $this->leads->pluck('id'))
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    public function render()
    {
        return view('livewire.frontend.home-parts.home-posts', [
            'categories'     => $this->categories,
            'leads'          => $this->leads,
            'ticker'         => $this->ticker,
            'latestPosts'    => $this->latestPosts,
            'popularPosts'   => $this->popularPosts,
            'firstSection'   => $this->firstSection,
            'mainSections'   => $this->mainSections,
            'wideSections'   => $this->wideSections,
            'special'        => $this->special,
            'notices'        => $this->notices,
            'opinions'       => $this->opinions,
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
