<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Post;

class CategoryPage extends Component
{
    use WithPagination;

    public Category $category;


    public function mount(string $slug): void
    {
        // Fail with 404 if not found
        $this->category = Category::where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $specialSlugs = [
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
            'libangshakha'
        ];

        $isSpecialCategory = in_array($this->category->slug, $specialSlugs, true);

        // Route specific slugs to their intended views
        $view = match ($this->category->slug) {
            'video' => 'livewire.frontend.video-page',
            default => $isSpecialCategory
                ? 'livewire.frontend.central-committee-page'
                : 'livewire.frontend.category-page',
        };

        if ($isSpecialCategory) {
            $posts = Post::with(['category'])
                ->where('category_id', $this->category->id)
                ->where('status', 'published')
                ->orderBy('published_at', 'asc')
                ->orderByDesc('id')
                ->get();
        } else {
            $posts = Post::with(['category'])
                ->where('category_id', $this->category->id)
                ->where('status', 'published')
                ->latest('published_at')
                ->paginate(9);
        }

        return view($view, [
            'category' => $this->category,
            'posts'    => $posts,
        ])->layout('components.layouts.frontend');
    }
    

}
