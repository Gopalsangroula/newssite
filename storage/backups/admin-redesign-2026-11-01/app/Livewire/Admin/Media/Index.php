<?php

namespace App\Livewire\Admin\Media;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Media;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteMedia($mediaId)
    {
        try {
            if (!auth()->user()->hasPermissionTo('delete media', 'web')) {
                Log::warning('Unauthorized media deletion attempt', [
                    'user_id' => auth()->id(),
                    'media_id' => $mediaId,
                ]);
                session()->flash('error', 'You are not authorized to delete media.');
                return;
            }

            $media = Media::findOrFail($mediaId);
            $folderPath = 'media/' . $media->created_at->format('Y/m') . '/' . $media->filename;
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->delete($folderPath);
            }
            $media->delete();

            Log::info('Media deleted successfully', [
                'media_id' => $mediaId,
                'user_id' => auth()->id(),
            ]);
            session()->flash('message', 'Media deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Media deletion failed: ' . $e->getMessage(), [
                'media_id' => $mediaId,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Failed to delete media: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Media::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('original_name', 'like', '%' . $this->search . '%')
                      ->orWhere('mime_type', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.admin.media.index', [
            'mediaItems' => $query->paginate($this->perPage),
        ]);
    }
}