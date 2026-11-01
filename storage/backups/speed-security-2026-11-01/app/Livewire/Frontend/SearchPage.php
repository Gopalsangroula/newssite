<?php

namespace App\Livewire\Frontend;

use App\Models\Post;
use App\Support\Seo;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SearchPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->q);

        $posts = Post::published()
            ->with(['category', 'users', 'createdBy'])
            ->where(function ($query) use ($term) {
                $query->where('title', 'like', '%'.$term.'%')
                    ->orWhere('sub_title', 'like', '%'.$term.'%')
                    ->orWhere('content', 'like', '%'.$term.'%');
            })
            ->latest('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        return view('livewire.frontend.search-page', [
            'term' => $term,
            // An empty search would match every post, so nothing is listed until there is a word to find.
            'posts' => $term === '' ? null : $posts,
        ])->layout('components.layouts.frontend', [
            'title' => 'खोज | '.Seo::site()['title_suffix'],
        ]);
    }
}
