<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

class AuthorPage extends Component
{
    use WithPagination;

    public $slug;
    public $author;
    public string $homeUrl;
    public string $title;
    public string $metaDescription;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->author = User::where('slug', $slug)
            ->with('roles')
            ->firstOrFail();

        $this->homeUrl = Route::has('frontend.welcome')
            ? route('frontend.welcome')
            : url('/');

        $this->title = $this->author->name . ' | ' . config('app.name');
        $this->metaDescription = $this->author->description ?? 'Posts by ' . $this->author->name . ' on ' . config('app.name');
    }

    public function render()
    {
        $posts = Post::published()
            ->where(function ($query) {
                $query->where('created_by', $this->author->id)
                      ->orWhereHas('users', fn($q) => $q->where('users.id', $this->author->id));
            })
            ->with(['category', 'users', 'createdBy'])
            ->latest('published_at')
            ->paginate(9);

        return view('livewire.frontend.author-page', [
            'author' => $this->author,
            'posts' => $posts,
            'homeUrl' => $this->homeUrl,
        ])
        ->layout('components.layouts.frontend', [
            'title' => $this->title,
            'metaDescription' => $this->metaDescription,
        ]);
    }
}