<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SinglePost extends Component
{
    public string $slug;
    public $post;
    public string $homeUrl;
    public string $title;
    public string $metaDescription;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $this->post = Post::published()
            ->where('slug', $slug)
            ->with([
                'category',
                'tags',
                'users',
                'createdBy',
                'publishedBy',
                'media'
            ])
            ->firstOrFail();

        $this->homeUrl = Route::has('frontend.welcome')
            ? route('frontend.welcome')
            : url('/');

        // Set SEO properties
        $this->title = $this->post->title . ' | ' . config('app.name');
        $this->metaDescription = $this->post->meta_description ?? 
            $this->post->sub_title ?? 
            Str::limit(strip_tags($this->post->content), 160);
    }

    public function getRelatedPostsProperty()
    {
        return Post::published()
            ->where('id', '!=', $this->post->id)
            ->where(function ($query) {
                $query->where('category_id', $this->post->category_id)
                      ->orWhereHas('tags', fn($q) => $q->whereIn('tags.id', $this->post->tags->pluck('id')));
            })
            ->with(['category', 'createdBy'])
            ->latest()
            ->take(3)
            ->get();
    }

    public function render()
    {
        // Ensure users is always a collection
        if (!$this->post->relationLoaded('users')) {
            $this->post->load('users');
        }
        if ($this->post->users === null || !($this->post->users instanceof \Illuminate\Support\Collection)) {
            $fallbackUser = $this->post->createdBy ?? $this->post->publishedBy ?? (object) ['name' => 'Unknown', 'slug' => '', 'user_image' => null];
            $this->post->setRelation('users', collect([$fallbackUser]));
        }

        return view('livewire.frontend.single-post', [
            'post' => $this->post,
            'homeUrl' => $this->homeUrl,
            'relatedPosts' => $this->relatedPosts,
            'commentCount' => $this->post->comments()->approved()->count(),
        ])
        ->layout('components.layouts.frontend', [
            'title' => $this->title,
            'metaDescription' => $this->metaDescription,
            'post' => $this->post, // Pass post to layout for meta tags
        ]);
    }
}