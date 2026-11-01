<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

class SinglePost extends Component
{
    public string $slug;
    public $post;
    public string $homeUrl;
    public string $title;
    public string $metaDescription;
    
    #[Validate('required|min:3|max:500')]
    public string $newComment = '';
    public bool $showComments = true;

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

    public function addComment()
    {
        $this->validate();

        if (!auth()->check()) {
            session()->flash('error', 'Please login to comment.');
            return;
        }

        Comment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->post->refresh();
        $this->dispatch('comment-added');
        session()->flash('success', 'Comment added successfully!');
    }

    public function toggleComments()
    {
        $this->showComments = !$this->showComments;
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
        ])
        ->layout('components.layouts.frontend', [
            'title' => $this->title,
            'metaDescription' => $this->metaDescription,
            'post' => $this->post, // Pass post to layout for meta tags
        ]);
    }
}