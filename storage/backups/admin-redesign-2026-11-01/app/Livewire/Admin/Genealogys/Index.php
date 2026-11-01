<?php

namespace App\Livewire\Admin\Genealogys;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Genealogy;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $listeners = [
        'genealogyCreated' => '$refresh',
        'genealogyUpdated' => '$refresh',
        'genealogyDeleted' => '$refresh',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Query members with search and pagination
        $members = Genealogy::query()
            ->when($this->search, function ($query) {
                $search = '%' . Str::lower(trim($this->search)) . '%';
                return $query->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(pustanumber) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(mother_name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(wife_name) LIKE ?', [$search]);
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.genealogys.index', [
            'members' => $members,
        ]);
    }
}