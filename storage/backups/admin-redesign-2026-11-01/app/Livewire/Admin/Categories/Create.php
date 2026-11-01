<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class Create extends Component
{
    public $name;
    public $slug;
    public $parent_id;
    public $categories;
    public $isLoading = false;
    public $showCancelModal = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'slug' => 'required|string|min:3|max:255|unique:categories,slug|regex:/^[a-z0-9-]+$/',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }

    protected $messages = [
        'name.regex' => 'The name can only contain letters, numbers, spaces, and hyphens.',
        'slug.regex' => 'The slug can only contain lowercase letters, numbers, and hyphens.',
    ];

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->name);
        $this->validateOnly('slug');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $this->isLoading = true;

        try {
            $this->validate();

            Category::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'parent_id' => $this->parent_id,
            ]);

            session()->flash('message', 'Category created successfully.');
            return redirect()->route('admin.categories.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create category: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    #[On('confirmCancel')]
    public function cancel()
    {
        return redirect()->route('admin.categories.index');
    }

    public function openCancelModal()
    {
        $this->showCancelModal = true;
    }

    public function render()
    {
        return view('livewire.admin.categories.create');
    }
}
