<?php

namespace App\Support;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * Everything the homepage shows. Each list is fetched once and kept for the rest of the request.
 */
class HomeFeed
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

    private ?Collection $categories = null;
    private ?Collection $leads = null;

    /**
     * The view data for the homepage.
     */
    public function data(): array
    {
        return [
            'categories' => $this->categories(),
            'leads' => $this->leads(),
            'ticker' => $this->ticker(),
            'latestPosts' => $this->latestPosts(),
            'popularPosts' => $this->popularPosts(),
            'firstSection' => $this->firstSection(),
            'mainSections' => $this->sections(self::MAIN_SECTIONS),
            'wideSections' => $this->sections(self::WIDE_SECTIONS),
            'special' => $this->fetchPosts('bishesh', 7),
            'notices' => $this->fetchPosts('notice', 5),
            'opinions' => $this->fetchPosts('drishtikon', 4)->load(['users', 'createdBy']),
            'publications' => $this->fetchPosts('publication', 4),
            'videos' => $this->fetchPosts('video', 4, wholeStory: true),
            'galleries' => $this->fetchPosts('photo-gallery', 5),
            'committee' => $this->committee(),
            'pageabout' => Page::published()->where('id', 1)->first(),
            'pagemessage' => Page::published()->where('slug', 'chairman-massage')->first(),
            'ads' => Advertisement::currentlyActive()->get()->groupBy('placement'),
        ];
    }

    private function categories(): Collection
    {
        return $this->categories ??= Category::get(['id', 'name', 'slug', 'parent_id'])->keyBy('slug');
    }

    /**
     * The stories across the top of the page: posts marked featured, or the newest news.
     */
    private function leads(): Collection
    {
        return $this->leads ??= $this->attachCategories(
            Post::published()
                ->forListing()
                ->where('is_featured', true)
                ->latest('published_at')
                ->take(self::MAX_LEAD_STORIES)
                ->get()
        )->whenEmpty(fn () => $this->attachCategories($this->news()->take(1)->get()));
    }

    /**
     * Posts marked breaking while they are still new, otherwise the newest news.
     */
    private function ticker(): array
    {
        $breaking = $this->news()
            ->where('is_breaking', true)
            ->where('published_at', '>=', now()->subDays(3))
            ->take(8)
            ->get();

        return $breaking->isNotEmpty()
            ? ['label' => 'ब्रेकिङ', 'posts' => $this->attachCategories($breaking)]
            : ['label' => 'ताजा', 'posts' => $this->latestPosts()];
    }

    private function latestPosts(): Collection
    {
        return once(fn () => $this->attachCategories($this->news()->take(8)->get()));
    }

    /**
     * The most read of the news published in the last 30 days.
     */
    private function popularPosts(): Collection
    {
        return $this->attachCategories($this->news()
            ->where('published_at', '>=', now()->subDays(30))
            ->where('views', '>', 0)
            ->reorder('views', 'desc')
            ->take(5)
            ->get());
    }

    /**
     * The section under the lead stories. Until the current affairs category
     * has posts, the newest news fills its place.
     */
    private function firstSection(): ?object
    {
        $current = $this->sections(['samasamayik' => ['icon' => 'bolt', 'layout' => 'feature', 'limit' => 5]])->first();

        if ($current) {
            return $current;
        }

        $posts = $this->attachCategories($this->news()->whereNotIn('id', $this->leads()->pluck('id'))->take(5)->get());

        return $posts->isEmpty() ? null : (object) [
            'title' => 'पछिल्लो अपडेट',
            'url' => route('frontend.category.show', 'news'),
            'icon' => 'rss',
            'layout' => 'feature',
            'posts' => $posts,
        ];
    }

    /**
     * Committee members, in the same order as their own page. Titles read "सचिव - नाम" or
     * just a name; anything longer is an article filed in the same category.
     */
    private function committee(): Collection
    {
        return Post::published()
            ->forListing()
            ->where('category_id', $this->categories()->get('centralcomittee')?->id)
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

    /**
     * The listed sections with their posts, without any that have none.
     */
    private function sections(array $sections): Collection
    {
        return collect($sections)
            ->map(fn ($section, $slug) => (object) ([
                'title' => $this->categories()->get($slug)?->name,
                'url' => route('frontend.category.show', $slug),
                'posts' => $this->fetchPosts($slug, $section['limit']),
            ] + $section))
            ->filter(fn ($section) => $section->posts->isNotEmpty());
    }

    /**
     * A category and the ones under it.
     */
    private function familyIds(string $slug): Collection
    {
        $category = $this->categories()->get($slug);

        return $category
            ? $this->categories()->where('parent_id', $category->id)->pluck('id')->push($category->id)
            : collect();
    }

    private function newsCategoryIds(): Collection
    {
        return collect(self::NEWS_CATEGORIES)->flatMap(fn ($slug) => $this->familyIds($slug));
    }

    private function news()
    {
        return Post::published()
            ->forListing()
            ->inCategories($this->newsCategoryIds())
            ->latest('published_at');
    }

    /**
     * Fetch published posts for a category, leaving out those already leading the page.
     */
    private function fetchPosts(string $categorySlug, int $limit, bool $wholeStory = false): Collection
    {
        return $this->attachCategories(
            Post::published()
                ->when(! $wholeStory, fn ($query) => $query->forListing())
                ->inCategories($this->familyIds($categorySlug))
                ->whereNotIn('id', $this->leads()->pluck('id'))
                ->latest('published_at')
                ->take($limit)
                ->get()
        );
    }

    /**
     * Every category is already in memory, so posts take theirs from there instead of a query each.
     */
    private function attachCategories(Collection $posts): Collection
    {
        $byId = once(fn () => $this->categories()->keyBy('id'));

        return $posts->each(function ($post) use ($byId) {
            if ($byId->has($post->category_id)) {
                $post->setRelation('category', $byId->get($post->category_id));
            }
        });
    }
}
