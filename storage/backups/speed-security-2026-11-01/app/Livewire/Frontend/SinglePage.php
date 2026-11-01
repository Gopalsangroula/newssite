<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Page;
use App\Models\Comment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

class SinglePage extends Component
{
    public string $slug;
    public $page;
    public string $homeUrl;
    public string $title;
    public string $metaDescription;
    
    #[Validate('required|min:3|max:500')]
    public string $newComment = '';
    public bool $showComments = false;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $this->page = Page::published()
            ->where('slug', $slug)
            ->with([
                'creator',
                'media'
            ])
            ->firstOrFail();

        $this->homeUrl = Route::has('frontend.welcome')
            ? route('frontend.welcome')
            : url('/');

        // Set SEO properties
        $this->title = $this->page->title . ' | ' . config('app.name');
        $this->metaDescription = $this->page->meta_description ?? 
            Str::limit(strip_tags($this->page->content), 160);
    }

    public function addComment()
    {
        $this->validate();

        if (!auth()->check()) {
            session()->flash('error', 'Please login to comment.');
            return;
        }

        if (method_exists($this->page, 'comments')) {
            Comment::create([
                'page_id' => $this->page->id,
                'user_id' => auth()->id(),
                'content' => $this->newComment,
            ]);

            $this->newComment = '';
            $this->page->refresh();
            $this->dispatch('comment-added');
            session()->flash('success', 'Comment added successfully!');
        } else {
            session()->flash('info', 'Commenting on pages is not available.');
        }
    }

    public function toggleComments()
    {
        $this->showComments = !$this->showComments;
    }

    public function getRelatedPagesProperty()
    {
        return Page::published()
            ->where('id', '!=', $this->page->id)
            ->with(['creator'])
            ->latest()
            ->take(3)
            ->get();
    }

    public function render()
        {
            $view = match ($this->page->slug) {
                'genealogy-book-pdf', 'pages/genealogy-book-pdf' => 'livewire.frontend.singlepdf-page',
                default => 'livewire.frontend.single-page',
            };

            return view($view, [
                'page'    => $this->page,
                'homeUrl' => $this->homeUrl,
            ])->layout('components.layouts.frontend', [
                'title'           => $this->title,
                'metaDescription' => $this->metaDescription,
                'page'            => $this->page,
            ]);
        }
}