<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Post;
use App\Support\Seo;

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

        // published() also leaves out future-dated posts, whose detail page would 404.
        $query = Post::published()
            ->with(['category', 'users', 'createdBy'])
            ->inCategories($this->category->familyIds());

        if ($isSpecialCategory) {
            $posts = $query
                ->orderBy('published_at', 'asc')
                ->orderByDesc('id')
                ->get();
        } else {
            $posts = $query
                ->latest('published_at')
                ->orderByDesc('id')
                ->paginate(9);
        }

        return view($view, [
            'category' => $this->category,
            'posts'    => $posts,
        ])->layout('components.layouts.frontend', [
            'title' => $this->category->name.' | '.Seo::site()['title_suffix'],
        ]);
    }
    

}
