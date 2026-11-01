<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tag;
use App\Models\Post;
use App\Support\Seo;

class TagPage extends Component
{
    use WithPagination;

    public $slug;
    public $tag;
    public $homeUrl;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->tag = Tag::where('slug', $slug)->firstOrFail();
        $this->homeUrl = route('frontend.welcome') ?? url('/');
    }

    public function render()
    {
        $posts = $this->tag->posts()
            ->published()
            ->with(['users', 'createdBy'])
            ->latest('published_at')
            ->paginate(9);

        return view('livewire.frontend.tag-page', [
            'tag' => $this->tag,
            'posts' => $posts,
            'homeUrl' => $this->homeUrl,
        ])->layout('components.layouts.frontend', [
            'title' => $this->tag->name.' | '.Seo::site()['title_suffix'],
        ]);
    }
}