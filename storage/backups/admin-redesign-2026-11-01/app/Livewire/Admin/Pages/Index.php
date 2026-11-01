<?php

namespace App\Livewire\Admin\Pages;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $showTrashed = false;
    public $confirmingDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'showTrashed' => ['except' => false],
    ];

    // Reset page on filter change
    public function updating($property)
    {
        if (in_array($property, ['search', 'status', 'showTrashed'])) {
            $this->resetPage();
        }
    }

    // Permission Helpers
    protected function canDelete(Page $page): bool
    {
        try {
            $user = Auth::user();

            if ($user->hasRole('super-admin')) {
                return true;
            }

            return $user->hasPermissionTo('delete content', 'web') ||
                   ($user->hasPermissionTo('delete own content', 'web') && $page->created_by === $user->id);
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission missing', ['user_id' => Auth::id(), 'exception' => $e->getMessage()]);
            return false;
        }
    }

    protected function canRestore(): bool
    {
        try {
            return Auth::user()->hasPermissionTo('restore content', 'web') ||
                   Auth::user()->hasRole('super-admin');
        } catch (PermissionDoesNotExist $e) {
            Log::error('Restore permission missing', ['user_id' => Auth::id(), 'exception' => $e->getMessage()]);
            return false;
        }
    }

    // Confirm Delete
    public function confirmDelete($pageId)
    {
        $page = Page::withTrashed()->find($pageId);

        if (!$page) {
            session()->flash('error', 'Page not found.');
            return;
        }

        if (!$this->canDelete($page)) {
            session()->flash('error', 'You are not authorized to delete this page.');
            return;
        }

        $this->confirmingDelete = $pageId;
        $this->dispatch('showDeleteModal');
    }

    // Delete (Soft or Force)
    public function delete()
    {
        if (!$this->confirmingDelete) {
            return;
        }

        $page = Page::withTrashed()->find($this->confirmingDelete);

        if (!$page) {
            session()->flash('error', 'Page not found.');
            $this->confirmingDelete = null;
            $this->dispatch('hideDeleteModal');
            return;
        }

        if (!$this->canDelete($page)) {
            session()->flash('error', 'Unauthorized action.');
            $this->confirmingDelete = null;
            $this->dispatch('hideDeleteModal');
            return;
        }

        try {
            // Delete cover image
            if ($page->cover_image && Storage::disk('public')->exists($page->cover_image)) {
                Storage::disk('public')->delete($page->cover_image);
            }

            // Delete related media (soft or hard)
            $page->media()->delete();

            // Force delete if trashed, otherwise soft delete
            if ($page->trashed()) {
                $page->forceDelete();
                session()->flash('message', 'Page permanently deleted.');
            } else {
                $page->delete();
                session()->flash('message', 'Page moved to trash.');
            }

            Log::info('Page deleted', [
                'page_id' => $page->id,
                'title' => $page->title,
                'deleted_by' => Auth::user()->name,
                'permanent' => $page->trashed(),
            ]);
        } catch (\Exception $e) {
            Log::error('Page delete failed', ['exception' => $e->getMessage()]);
            session()->flash('error', 'Failed to delete page.');
        }

        $this->confirmingDelete = null;
        $this->dispatch('hideDeleteModal');
    }

    // Restore
    public function restore($pageId)
    {
        $page = Page::withTrashed()->find($pageId);

        if (!$page || !$page->trashed()) {
            session()->flash('error', 'Page not found or not trashed.');
            return;
        }

        if (!$this->canRestore()) {
            session()->flash('error', 'You are not authorized to restore pages.');
            return;
        }

        $page->restore();

        session()->flash('message', 'Page restored successfully.');
        Log::info('Page restored', ['page_id' => $page->id, 'restored_by' => Auth::user()->name]);
    }

    public function render()
    {
        $query = Page::query()
            ->with('creator')
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->showTrashed, fn($q) => $q->withTrashed());

        // Editor sees only own content
        if (Auth::user()->hasRole('editor')) {
            $query->where('created_by', Auth::id());
        }

        $pages = $query->latest()->paginate(15);

        return view('livewire.admin.pages.index', compact('pages'));
    }
}