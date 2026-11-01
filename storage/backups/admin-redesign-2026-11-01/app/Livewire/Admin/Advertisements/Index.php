<?php

namespace App\Livewire\Admin\Advertisements;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Advertisement;

class Index extends Component
{
    use WithPagination;

    public $sortField = 'title';
    public $sortDirection = 'asc';

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
     * Delete an advertisement.
     */
    public function delete($id)
    {
        try {
            $ad = Advertisement::findOrFail($id);
            $ad->delete();
            $this->dispatch('success', message: 'Advertisement deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Failed to delete advertisement: ' . $e->getMessage());
        }
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