<?php

namespace App\Livewire\Admin\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Add Media')]
class Create extends Component
{
    use AuthorizesMedia, WithPagination;

    public $fileTypeFilter = '';

    public function updatedFileTypeFilter()
    {
        $this->resetPage();
    }

    #[On('upload-complete')]
    public function showNewUploads()
    {
        $this->reset('fileTypeFilter');
        $this->resetPage();
    }

    #[On('delete-media')]
    public function delete($id)
    {
        if (! $this->canDeleteMedia()) {
            Log::warning('Unauthorized media deletion attempt', [
                'user_id' => auth()->id(),
                'media_id' => $id,
            ]);
            $this->dispatch('media-notice', message: 'You are not authorized to delete media.', status: 'danger');
            return;
        }

        try {
            Media::findOrFail($id)->deleteWithFile();
            $this->dispatch('media-notice', message: 'File deleted successfully.', status: 'success');
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            $this->dispatch('media-notice', message: 'Failed to delete file.', status: 'danger');
        }
    }

    public function render()
    {
        $mediaFiles = Media::with('mediable')
            ->when(array_key_exists($this->fileTypeFilter, Media::KINDS), function ($query) {
                $query->ofKind($this->fileTypeFilter);
            })
            ->latest()
            ->latest('id')
            ->paginate(12);

        return view('livewire.admin.media.create', [
            'mediaFiles' => $mediaFiles,
            'canDelete' => $this->canDeleteMedia(),
            'canEdit' => $this->canEditMedia(),
        ]);
    }
}
