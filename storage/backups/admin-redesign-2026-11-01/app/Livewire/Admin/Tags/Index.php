<?php

namespace App\Livewire\Admin\Tags;

use Livewire\Component;
use App\Models\Tag;

class Index extends Component
{
    public $tags;

    public function mount()
    {
        $this->tags = Tag::all();
    }

    public function delete($id)
    {
        Tag::findOrFail($id)->delete();
        $this->tags = Tag::all();
        session()->flash('message', 'Tag deleted successfully.');
    }
    public function render()
    {
        return view('livewire.admin.tags.index');
    }
}
