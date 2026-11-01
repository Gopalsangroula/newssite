<?php

namespace App\Livewire\Frontend\HomeParts;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Post;
use App\Models\Page;
use App\Models\Genealogy;

class HomePosts extends Component
{
    public int $bannerCatId = 6;
    public int $newsCatId = 5;
    public int $politicsCatId = 2;
    public int $arthaCatId = 5;
    public int $entCatId = 4;
    public int $genLimit = 6;
    public int $eventId = 18;

    #[Computed]
    public function allNewsPosts()
    {
        return $this->fetchPosts($this->newsCatId, 7);
    }

    #[Computed]
    public function bannerPosts()
    {
        return $this->fetchPosts($this->bannerCatId, 3);
    }

    #[Computed]
    public function newsGenealogy()
    {     
    return Genealogy::with(['father', 'children'])
        ->where('pustanumber', '>=', 13)   
        ->orderBy('pustanumber', 'asc')
        ->take(5)
        ->get();
    }
    #[Computed]
    public function pageabout()
    {
         return Page::published() ->where('id', 1) ->first();

    }
    #[Computed]
    public function pagemessage()
    {
         return Page::published() ->where('slug', 'chairman-massage') ->first();

    }

    #[Computed]
    public function newsPosts()
    {
        return $this->allNewsPosts()->take(6);
    }

    #[Computed]
    public function secondaryPosts()
    {
        return $this->allNewsPosts()->skip(4)->take(3);
    }

    #[Computed]
    public function politicsPosts()
    {
        return $this->fetchPosts($this->politicsCatId, 7);
    }

    #[Computed]
    public function arthaPosts()
    {
        return $this->fetchPosts($this->arthaCatId, 6);
    }

    #[Computed]
    public function entPosts()
    {
        return $this->fetchPosts($this->entCatId, 6);
    }
    #[Computed]
    public function eventPosts()
    {
        return $this->fetchPosts($this->eventId, 1);
    }

    #[Computed]
    public function latestPosts()
    {
        return Post::published()
            ->with(['category'])
            ->latest('published_at')
            ->take(5)
            ->get();
    }

    /**
     * Fetch published posts for a category.
     */
    private function fetchPosts(int $categoryId, int $limit)
    {
        return Post::published()
            ->with(['category'])
            ->where('category_id', $categoryId)
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    public function render()
    {
        return view('livewire.frontend.home-parts.home-posts', [
            'bannerPosts'     => $this->bannerPosts(),
            'newsPosts'       => $this->newsPosts(),
            'secondaryPosts'  => $this->secondaryPosts(),
            'politicsPosts'   => $this->politicsPosts(),
            'latestPosts'     => $this->latestPosts(),
            'arthaPosts'      => $this->arthaPosts(),
            'entPosts'        => $this->entPosts(),
            'eventPosts'        => $this->eventPosts(),
            'newsGenealogy'   => $this->newsGenealogy(),
            'pageabout'   => $this->pageabout(),
            'pagemessage'   => $this->pagemessage(),
        ]);
    }
}