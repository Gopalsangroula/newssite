<?php

namespace App\Livewire\Admin\Advertisements;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Advertisement;

#[Title('Advertisements')]
class Index extends Component
{
    use WithPagination;

    public $sortField = 'title';
    public $sortDirection = 'asc';
    public $deleteId = null;

    protected $listeners = [
        'advertisementCreated' => '$refresh', // Refresh on create
    ];

    /**
     * Sort table by column.
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Ask for confirmation before deleting.
     */
    public function confirmDelete($id)
    {
        $ad = Advertisement::find($id);

        if (! $ad) {
            session()->flash('error', 'Advertisement not found.');
            return;
        }

        $this->deleteId = $ad->id;
        $this->dispatch(
            'showDeleteModal',
            message: "Are you sure you want to delete {$ad->title}? This action cannot be undone."
        );
    }

    /**
     * Delete an advertisement.
     */
    public function delete()
    {
        try {
            $ad = Advertisement::findOrFail($this->deleteId);
            $ad->delete();

            $this->resetPage();
            session()->flash('message', 'Advertisement deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete advertisement: ' . $e->getMessage());
        }

        $this->deleteId = null;
        $this->dispatch('hideDeleteModal');
    }


    public function render()
    {
        $ads = Advertisement::orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.advertisements.index', [
            'ads' => $ads,
        ]);
    }
}
