<?php

namespace App\Livewire\Admin\Tags;

use Livewire\Component;
use App\Models\Tag;
use Illuminate\Support\Str;
class Create extends Component
{
     public $name, $slug;

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|unique:tags,slug',
    ];

    public function updatedName()
    {
        $this->slug = Str::slug($this->name);
    }

    public function save()
    {
        $this->validate();

        Tag::create([
            'name' => $this->name,
            'slug' => $this->slug,
        ]);

        session()->flash('message', 'Tag created successfully.');
        return redirect()->route('admin.tags.index');
    }


    public function render()
    {
        return view('livewire.admin.tags.create');
    }
}
