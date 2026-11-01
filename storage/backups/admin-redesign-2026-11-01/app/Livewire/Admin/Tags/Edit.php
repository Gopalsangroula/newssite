<?php

namespace App\Livewire\Admin\Tags;

use Livewire\Component;
use App\Models\Tag;
use Illuminate\Support\Str;
class Edit extends Component
{
        public $tag;
    public $name, $slug;

    public function mount(Tag $tag)
    {
        $this->tag = $tag;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tags,slug,' . $this->tag->id,
        ];
    }

    public function updatedName()
    {
        $this->slug = Str::slug($this->name);
    }

    public function update()
    {
        $this->validate();

        $this->tag->update([
            'name' => $this->name,
            'slug' => $this->slug,
        ]);

        session()->flash('message', 'Tag updated successfully.');
        return redirect()->route('admin.tags.index');
    }
    public function render()
    {
        return view('livewire.admin.tags.edit');
    }
}
