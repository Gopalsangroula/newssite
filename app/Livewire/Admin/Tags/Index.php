<?php

namespace App\Livewire\Admin\Tags;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tag;
use Illuminate\Support\Str;

#[Title('Tags')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $tag = Tag::withCount('posts')->find($id);

        if (! $tag) {
            session()->flash('error', 'Tag not found.');
            return;
        }

        $message = "Are you sure you want to delete {$tag->name}?";

        if ($tag->posts_count > 0) {
            $message .= " It will be removed from {$tag->posts_count} " . Str::plural('post', $tag->posts_count) . '. The posts are kept.';
        }

        $this->deleteId = $tag->id;
        $this->dispatch('showDeleteModal', message: $message . ' This action cannot be undone.');
    }

    public function delete()
    {
        try {
            Tag::findOrFail($this->deleteId)->delete();

            $this->resetPage();
            session()->flash('message', 'Tag deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete tag: ' . $e->getMessage());
        }

        $this->deleteId = null;
        $this->dispatch('hideDeleteModal');
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.admin.tags.index', [
            'tags' => Tag::withCount('posts')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(10),
        ]);
    }
}
