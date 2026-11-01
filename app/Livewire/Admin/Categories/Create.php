<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;

#[Title('Add Category')]
class Create extends Component
{
    public $name;
    public $slug;
    public $parent_id;
    public $categories;
    public $isLoading = false;

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
        // Outside the try, or the catch below turns field errors into one flash message
        $this->validate();

        $this->isLoading = true;

        try {
            Category::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'parent_id' => $this->parent_id ?: null,
            ]);

            session()->flash('message', 'Category created successfully.');
            return redirect()->route('admin.categories.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create category: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.categories.create');
    }
}
