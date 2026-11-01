<?php

namespace App\Livewire\Admin\Comments;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Comment;

#[Title('Comments')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function approve($id)
    {
        Comment::findOrFail($id)->update(['is_approved' => true]);

        session()->flash('message', 'Comment approved. It now shows on the post.');
    }

    public function unapprove($id)
    {
        Comment::findOrFail($id)->update(['is_approved' => false]);

        session()->flash('message', 'Comment hidden from the post.');
    }

    public function confirmDelete($id)
    {
        $comment = Comment::find($id);

        if (! $comment) {
            session()->flash('error', 'Comment not found.');
            return;
        }

        $this->deleteId = $comment->id;
        $this->dispatch(
            'showDeleteModal',
            message: "Are you sure you want to delete this comment by {$comment->author_name}? This action cannot be undone."
        );
    }

    public function delete()
    {
        try {
            Comment::findOrFail($this->deleteId)->delete();

            $this->resetPage();
            session()->flash('message', 'Comment deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete comment: ' . $e->getMessage());
        }

        $this->deleteId = null;
        $this->dispatch('hideDeleteModal');
    }

    public function render()
    {
        $search = trim($this->search);

        // A comment stays in the list after its post goes to the trash
        $comments = Comment::with(['post' => fn ($query) => $query->withTrashed()->select('id', 'title', 'deleted_at')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('author_name', 'like', '%' . $search . '%')
                        ->orWhere('author_email', 'like', '%' . $search . '%')
                        ->orWhere('body', 'like', '%' . $search . '%')
                        ->orWhereHas('post', fn ($post) => $post->withTrashed()->where('title', 'like', '%' . $search . '%'));
                });
            })
            ->when($this->status === 'pending', fn ($query) => $query->pending())
            ->when($this->status === 'approved', fn ($query) => $query->approved())
            // Comments waiting for approval come first, so that none get missed
            ->orderBy('is_approved')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.comments.index', [
            'comments' => $comments,
            'pendingCount' => Comment::pending()->count(),
        ]);
    }
}
