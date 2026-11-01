<?php

namespace App\Livewire\Admin\Genealogys;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Genealogy;
use Illuminate\Support\Facades\Storage;

#[Title('Genealogy')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $generationFilter = '';
    public $sexFilter = '';
    public $perPage = 10;
    public $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'generationFilter' => ['except' => ''],
        'sexFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'genealogyCreated' => '$refresh',
        'genealogyUpdated' => '$refresh',
        'genealogyDeleted' => '$refresh',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedGenerationFilter()
    {
        $this->resetPage();
    }

    public function updatedSexFilter()
    {
        $this->resetPage();
    }

    public function confirmDelete($memberId)
    {
        $member = Genealogy::find($memberId);

        if (! $member) {
            session()->flash('error', 'Member not found.');
            return;
        }

        if (Genealogy::where('father_id', $member->id)->exists()) {
            session()->flash('error', "Cannot delete {$member->name}: They are referenced as a father.");
            return;
        }

        $this->deleteId = $member->id;
        $this->dispatch(
            'showDeleteModal',
            message: "Are you sure you want to delete {$member->name} (Pusta Number: {$member->pustanumber})? This action cannot be undone."
        );
    }

    public function deleteMember()
    {
        try {
            $member = Genealogy::findOrFail($this->deleteId);

            // Check if member is referenced as a father
            if (Genealogy::where('father_id', $member->id)->exists()) {
                session()->flash('error', 'Cannot delete member: They are referenced as a father.');
            } else {
                // Delete associated image if exists
                if ($member->image) {
                    Storage::disk('public')->delete($member->image);
                }

                $member->delete();

                session()->flash('message', 'Member deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete member: ' . $e->getMessage());
        }

        $this->deleteId = null;
        $this->dispatch('hideDeleteModal');
    }

    protected function membersQuery()
    {
        $search = trim($this->search);

        return Genealogy::query()
            ->with('father:id,name')
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($this->generationFilter !== '', fn ($query) => $query->where('pustanumber', $this->generationFilter))
            ->when($this->sexFilter !== '', fn ($query) => $query->where('sex', $this->sexFilter))
            ->orderBy('name')
            ->orderBy('id');
    }

    public function render()
    {
        $members = $this->membersQuery()->paginate($this->perPage);

        // Deleting the last row of the last page would otherwise leave an empty page
        if ($members->isEmpty() && $members->currentPage() > 1) {
            $this->setPage($members->lastPage());
            $members = $this->membersQuery()->paginate($this->perPage);
        }

        return view('livewire.admin.genealogys.index', [
            'members' => $members,
            'generations' => Genealogy::query()->distinct()->orderBy('pustanumber')->pluck('pustanumber'),
        ]);
    }
}
