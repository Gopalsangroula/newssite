<?php

namespace App\Livewire\Admin\Posts;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $showTrashed = false;
    public $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'showTrashed' => ['except' => false],
    ];

    /** ------------------------------
     *  Filter updates
     * -----------------------------*/
    public function updatingSearch()       { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }
    public function updatingShowTrashed()  { $this->resetPage(); }

    /** ------------------------------
     *  Permission check helpers
     * -----------------------------*/
    protected function canDeletePost($post): bool
    {
        try {
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            return true; // super-admin bypasses permission check
        }

        return $user->hasPermissionTo('delete posts', 'web') ||
            ($user->hasPermissionTo('delete own posts', 'web') && $post->created_by === $user->id);
    } catch (PermissionDoesNotExist $e) {
        Log::error('Delete permission missing in DB', [
            'user_id' => Auth::id(),
            'exception' => $e->getMessage(),
        ]);
        return false;
    }
    }

    protected function canRestorePosts(): bool
    {
        try {
            return Auth::user()->hasPermissionTo('restore posts', 'web');
        } catch (PermissionDoesNotExist $e) {
            Log::error('Restore permission missing in DB', [
                'user_id' => Auth::id(),
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /** ------------------------------
     *  Post deletion
     * -----------------------------*/
public function confirmDelete($postId)
{
    Log::debug('Confirm delete called', ['post_id' => $postId, 'user_id' => Auth::id()]);

    if (!Auth::check()) {
        session()->flash('error', 'You must be logged in to perform this action.');
        return;
    }

    $post = Post::withTrashed()->find($postId);
    if (!$post) {
        session()->flash('error', 'Post not found.');
        return;
    }

    if (!$this->canDeletePost($post)) {
        Log::warning('Unauthorized post deletion attempt', [
            'user_id' => Auth::id(),
            'post_id' => $postId,
        ]);
        session()->flash('error', 'You are not authorized to delete this post.');
        return;
    }

    $this->deleteId = $postId;
    Log::debug('Delete ID set', ['post_id' => $this->deleteId]);
    $this->dispatch('showDeleteModal');
}



public function deletePost()
{
    if (!$this->deleteId) {
        Log::error('Delete ID is null', ['user_id' => Auth::id()]);
        session()->flash('error', 'Invalid post ID.');
        $this->dispatch('hideDeleteModal');
        return;
    }

    Log::info('Attempting to move post to trash', [
        'user_id' => Auth::id(),
        'post_id' => $this->deleteId,
    ]);

    try {
        $post = Post::withTrashed()->findOrFail($this->deleteId);

        if (!$this->canDeletePost($post)) {
            Log::warning('Unauthorized post deletion attempt', [
                'user_id' => Auth::id(),
                'post_id' => $this->deleteId,
            ]);
            session()->flash('error', 'You are not authorized to delete this post.');
            $this->dispatch('hideDeleteModal');
            return;
        }

        // Log relationships before deletion
        Log::debug('Relationships before deletion', [
            'post_id' => $post->id,
            'media_count' => $post->media()->count(),
            'tags_count' => $post->tags()->count(),
            'users_count' => $post->users()->count(),
        ]);

        // Clean relationships
        $post->media()->delete(); // Soft delete if Media uses SoftDeletes, else hard delete
        $post->tags()->detach();  // Detach tags from post_tag pivot
        $post->users()->detach(); // Detach users from post_user pivot
        $post->delete();          // Soft delete the post

        Log::info('Post moved to trash', [
            'post_id' => $post->id,
            'title' => $post->title,
            'deleted_by' => Auth::user()->name . ' (ID: ' . Auth::id() . ')',
        ]);

        session()->flash('message', 'Post moved to trash successfully.');
    } catch (\Exception $e) {
        Log::error('Failed to move post to trash', [
            'post_id' => $this->deleteId,
            'exception' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
        ]);
        session()->flash('error', 'Failed to move post to trash: ' . $e->getMessage());
    }

    $this->deleteId = null;
    $this->dispatch('hideDeleteModal');
}
    /** ------------------------------
     *  Post restoration
     * -----------------------------*/
    public function restorePost($postId)
    {
        if (!Auth::check()) {
            session()->flash('error', 'You must be logged in to perform this action.');
            return;
        }

        if (!$this->canRestorePosts()) {
            Log::warning('Unauthorized post restoration attempt', [
                'user_id' => Auth::id(),
                'post_id' => $postId,
            ]);
            session()->flash('error', 'You are not authorized to restore posts.');
            return;
        }

        try {
            $post = Post::withTrashed()->findOrFail($postId);
            $post->restore();

            Log::info('Post restored', [
                'post_id' => $post->id,
                'title' => $post->title,
                'restored_by' => Auth::user()->name . ' (ID: ' . Auth::id() . ')',
                'created_by' => optional($post->createdBy)->name . ' (ID: ' . ($post->created_by ?? 'N/A') . ')',
                'authors' => $post->users->pluck('name', 'id')->toArray(),
            ]);

            session()->flash('message', 'Post restored successfully.');
        } catch (\Exception $e) {
            Log::error('Post restoration failed: ' . $e->getMessage(), [
                'post_id' => $postId,
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);
            session()->flash('error', 'Failed to restore post: ' . $e->getMessage());
        }
    }

    /** ------------------------------
     *  Render
     * -----------------------------*/
  public function render()
{
    $user = Auth::user();

    $user = Auth::user();

    $query = Post::with(['category', 'createdBy', 'users']);

    // Include trashed if requested
    if ($this->showTrashed) {
        $query->withTrashed();
    }

    // Role restriction
    if ($user->hasRole('editor')) {
        $query->where(function($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('users', fn($uq) => $uq->where('users.id', $user->id));
        });
    }

    // Search and filters
    if ($this->search) {
        $query->where('title', 'like', '%' . $this->search . '%');
    }

    if ($this->statusFilter) {
        $query->where('status', $this->statusFilter);
    }

    if ($this->categoryFilter) {
        $query->where('category_id', $this->categoryFilter);
    }

    $query->orderBy('created_at', 'desc');

    return view('livewire.admin.posts.index', [
        'posts' => $query->paginate(10),
        'categories' => Category::select('id', 'name')->get(),
    ]);
}
}
