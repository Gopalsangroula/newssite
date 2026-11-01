<?php

namespace App\Livewire\Admin\Comments;

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Comment;

#[Title('Edit Comment')]
class Edit extends Component
{
    public $commentId;
    public $author_name;
    public $author_email;
    public $body;

    protected $rules = [
        'author_name' => 'required|string|max:255',
        'author_email' => 'required|email|max:255',
        'body' => 'required|string|max:5000',
    ];

    public function mount($id)
    {
        $comment = Comment::findOrFail($id);

        $this->commentId = $comment->id;
        $this->author_name = $comment->author_name;
        $this->author_email = $comment->author_email;
        $this->body = $comment->body;
    }

    public function update()
    {
        $validated = $this->validate();

        Comment::findOrFail($this->commentId)->update($validated);

        session()->flash('message', 'Comment updated successfully.');
        return redirect()->route('admin.comments.index');
    }

    public function render()
    {
        return view('livewire.admin.comments.edit', [
            'comment' => Comment::with(['post' => fn ($query) => $query->withTrashed()->select('id', 'title', 'deleted_at')])
                ->findOrFail($this->commentId),
        ]);
    }
}
