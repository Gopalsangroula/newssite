<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $isLoading = false;

    protected $paginationTheme = 'tailwind'; // or 'bootstrap' if you use bootstrap

    public function delete($id)
    {
        $this->isLoading = true;

        try {
            $category = Category::findOrFail($id);
            $category->delete();

            $this->resetPage(); // reset pagination after delete
            session()->flash('message', 'Category deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete category: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.categories.index', [
            'categories' => Category::with('parent')
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }
}
