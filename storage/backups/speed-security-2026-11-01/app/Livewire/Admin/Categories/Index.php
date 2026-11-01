<?php

namespace App\Livewire\Admin\Categories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Str;
use Livewire\WithPagination;

#[Title('Categories')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $deleteId = null;
    public $isLoading = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $category = Category::withCount('children')->find($id);

        if (! $category) {
            session()->flash('error', 'Category not found.');
            return;
        }

        $postCount = Post::where('category_id', $category->id)->count();

        $message = "Are you sure you want to delete {$category->name}?";

        if ($postCount > 0) {
            $message .= " Its {$postCount} " . Str::plural('post', $postCount) . ' will not be deleted, but will show as Uncategorized.';
        }

        if ($category->children_count > 0) {
            $message .= " Its {$category->children_count} " . Str::plural('sub-category', $category->children_count) . ' will become top level.';
        }

        $this->deleteId = $category->id;
        $this->dispatch('showDeleteModal', message: $message);
    }

    public function delete()
    {
        $this->isLoading = true;

        try {
            $category = Category::findOrFail($this->deleteId);
            $category->delete();

            $this->resetPage(); // reset pagination after delete
            session()->flash('message', 'Category deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete category: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }

        $this->deleteId = null;
        $this->dispatch('hideDeleteModal');
    }

    /**
     * Put each category straight after its parent and record how deep it sits.
     * A category with a deleted parent counts as top level. One caught in a parent
     * loop has no top level to hang from, so the second pass adds it at the end.
     */
    private function inTreeOrder(Collection $categories): Collection
    {
        $present = $categories->pluck('id')->flip();
        $byParent = $categories->groupBy(function ($category) use ($present) {
            return $category->parent_id && $present->has($category->parent_id) ? $category->parent_id : 'top';
        });

        $ordered = collect();

        $add = function ($category, $depth) use (&$add, $byParent, $ordered) {
            if ($ordered->has($category->id)) {
                return;
            }

            $category->depth = $depth;
            $ordered->put($category->id, $category);

            foreach ($byParent->get($category->id, []) as $child) {
                $add($child, $depth + 1);
            }
        };

        foreach ($byParent->get('top', []) as $category) {
            $add($category, 0);
        }

        foreach ($categories as $category) {
            $add($category, 0);
        }

        return $ordered->values();
    }

    public function render()
    {
        $search = trim($this->search);

        // Category has no posts() relation, so the count is a subquery. Trashed posts are left out.
        $categories = Category::with('parent:id,name')
            ->addSelect(['posts_count' => Post::selectRaw('count(*)')->whereColumn('posts.category_id', 'categories.id')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Search results are a flat list: their parents may not be among them
        $rows = $search === '' ? $this->inTreeOrder($categories) : $categories;

        $perPage = 10;
        $page = $this->getPage();

        return view('livewire.admin.categories.index', [
            'categories' => new LengthAwarePaginator($rows->forPage($page, $perPage)->values(), $rows->count(), $perPage, $page),
        ]);
    }
}
