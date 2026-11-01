<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\Seo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The public pages. These routes have no session, so nothing here may depend on who is asking.
 */
class SiteController extends Controller
{
    private const PER_PAGE = 9;
    private const COMMENTS_SHOWN = 50;

    /**
     * Committee and branch categories list everyone on one page, oldest first.
     */
    private const COMMITTEE_CATEGORIES = [
        'centralcomittee',
        'upatyaka',
        'sangkhuwasabha',
        'sunsari',
        'jhapamorang',
        'phalelung',
        'phungling',
        'phulbari',
        'phakumba',
        'video',
        'libangshakha',
    ];

    public function post(string $slug): View
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['category', 'tags', 'users', 'createdBy', 'publishedBy'])
            ->firstOrFail();

        $this->requireExactSlug('frontend.details.show', $post->slug, $slug);

        $approved = Comment::approved()->where('post_id', $post->id);

        return $this->show('site.single-post', [
            'title' => $post->title.' | '.config('app.name'),
            'metaDescription' => $post->meta_description ?? $post->sub_title ?? Str::limit(strip_tags($post->content), 160),
            'post' => $post,
            'homeUrl' => route('frontend.welcome'),
            'commentCount' => $approved->count(),
            // Only what the page prints, so a reader's email can never reach the HTML.
            'comments' => $approved->latest()->orderByDesc('id')->take(self::COMMENTS_SHOWN)->get(['id', 'author_name', 'body', 'created_at']),
        ]);
    }

    public function page(string $slug): View
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $this->requireExactSlug('frontend.frontend.pages.show', $page->slug, $slug);

        return $this->show($slug === 'genealogy-book-pdf' ? 'site.singlepdf-page' : 'site.single-page', [
            'title' => $page->title.' | '.config('app.name'),
            'metaDescription' => $page->meta_description ?? Str::limit(strip_tags($page->content), 160),
            'page' => $page,
            'homeUrl' => route('frontend.welcome'),
        ]);
    }

    public function author(string $slug): View
    {
        $author = User::where('slug', $slug)->firstOrFail();

        $this->requireExactSlug('frontend.author.show', $author->slug, $slug);

        return $this->show('site.author-page', [
            'title' => $author->name.' | '.config('app.name'),
            'metaDescription' => $author->description ?? 'Posts by '.$author->name.' on '.config('app.name'),
            'author' => $author,
            'homeUrl' => route('frontend.welcome'),
            'posts' => $this->listing(
                Post::published()
                    ->forListing()
                    ->where(function ($query) use ($author) {
                        $query->where('created_by', $author->id)
                            ->orWhereHas('users', fn ($query) => $query->where('users.id', $author->id));
                    })
                    ->with(['category', 'users', 'createdBy'])
                    ->latest('published_at')
            ),
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $this->requireExactSlug('frontend.tag.show', $tag->slug, $slug);

        return $this->show('site.tag-page', [
            'title' => $tag->name.' | '.Seo::site()['title_suffix'],
            'tag' => $tag,
            'posts' => $this->listing(
                $tag->posts()
                    ->published()
                    ->forListing()
                    ->with(['users', 'createdBy'])
                    ->latest('published_at')
            ),
        ]);
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $this->requireExactSlug('frontend.category.show', $category->slug, $slug);

        $isCommittee = in_array($category->slug, self::COMMITTEE_CATEGORIES, true);
        $isVideo = $category->slug === 'video';

        // These list everyone on one page, so any higher page number is made up.
        abort_if($isCommittee && $request->integer('page') > 1, 404);

        $view = match (true) {
            $isVideo => 'site.video-page',
            $isCommittee => 'site.central-committee-page',
            default => 'site.category-page',
        };

        // published() also leaves out future-dated posts, whose detail page would 404.
        $query = Post::published()
            ->with(['category', 'users', 'createdBy'])
            ->inCategories($category->familyIds());

        return $this->show($view, [
            'title' => $category->name.' | '.Seo::site()['title_suffix'],
            'category' => $category,
            'posts' => $isCommittee
                // Video cards look inside each story for its YouTube link, so those keep their whole text.
                ? $query->when(! $isVideo, fn ($query) => $query->forListing())
                    ->orderBy('published_at', 'asc')
                    ->orderByDesc('id')
                    ->get()
                : $this->listing($query->forListing()->latest('published_at')->orderByDesc('id')),
        ]);
    }

    public function search(Request $request): View
    {
        $query = $request->query('q');
        $term = is_string($query) ? Str::limit(trim($query), 100, '') : '';

        return $this->show('site.search-page', [
            'title' => 'खोज | '.Seo::site()['title_suffix'],
            'term' => $term,
            // An empty search would match every post, so nothing is listed until there is a word to find.
            'posts' => $term === '' ? null : Post::published()
                ->forListing()
                ->with(['category', 'users', 'createdBy'])
                ->search($term)
                ->latest('published_at')
                ->orderByDesc('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
        ]);
    }

    /**
     * The database ignores capitals when it looks up a slug, so /NEWS finds the same row as /news.
     * Those go to the one real address, or each spelling would be built and stored as its own page.
     */
    private function requireExactSlug(string $route, string $real, string $asked): void
    {
        if ($real !== $asked) {
            throw new HttpResponseException(redirect()->route($route, $real, 301));
        }
    }

    /**
     * Wraps a page in the public layout. Layout values and the page's own data travel together.
     */
    private function show(string $content, array $data): View
    {
        return view('site.frame', ['content' => $content] + $data);
    }

    /**
     * One page of posts. A page past the end is a 404, so made-up page numbers are never stored.
     */
    private function listing($query): LengthAwarePaginator
    {
        $posts = $query->paginate(self::PER_PAGE);

        abort_if($posts->isEmpty() && $posts->currentPage() > 1, 404);

        return $posts;
    }
}
