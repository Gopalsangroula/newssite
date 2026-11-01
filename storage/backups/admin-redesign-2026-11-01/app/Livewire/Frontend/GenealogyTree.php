<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Genealogy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class GenealogyTree extends Component
{
    public $rootMembers;
    public $allMembers;
    public $filteredMembers;
    public $selectedMemberId;
    public $searchQuery = '';
    public $selectedMemberDetails = null;

    public function mount()
    {
        // Cache root members for 1 hour to reduce database queries
        $this->rootMembers = Cache::remember('root_members', 3600, function () {
            return Genealogy::whereNull('father_id')->orderBy('name')->get();
        });

        // Cache all members with children for 1 hour
        $this->allMembers = Cache::remember('all_members', 3600, function () {
            return Genealogy::with('children')->orderBy('name')->get();
        });

        $this->filteredMembers = $this->allMembers;
    }

    public function updatedSearchQuery($value)
    {
        // Validate search query length
        if (strlen($value) < 2 && !empty($value)) {
            session()->flash('error', 'Search query must be at least 2 characters.');
            $this->filteredMembers = $this->allMembers;
            return;
        }

        if (empty($value)) {
            $this->filteredMembers = $this->allMembers;
            return;
        }

        $this->filteredMembers = $this->allMembers->filter(function ($member) use ($value) {
            return str_contains(strtolower($member->name), strtolower($value)) ||
                   str_contains(strtolower($member->pustanumber), strtolower($value));
        })->values();

        // Auto-select if only one result
        if ($this->filteredMembers->count() === 1) {
            $this->selectedMemberId = $this->filteredMembers->first()->id;
        }
    }

    public function showTree()
    {
        if (!$this->selectedMemberId) {
            session()->flash('error', 'Please select a member first.');
            return;
        }

        try {
            $person = Genealogy::with(['children' => function ($query) {
                $query->with('children')->lazy(); // Lazy load children for performance
            }])->findOrFail($this->selectedMemberId);

            $tree = $this->buildTree($person);

            \Log::info('Tree built for member:', [
                'id' => $this->selectedMemberId,
                'name' => $person->name,
                'children_count' => count($tree['children'] ?? [])
            ]);

            $this->dispatch('load-tree', tree: $tree);
            session()->flash('message', 'Tree loaded successfully for ' . $person->name);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Selected member not found.');
            $this->dispatch('load-tree', tree: null);
        } catch (\Exception $e) {
            \Log::error('Failed to load genealogy tree: ' . $e->getMessage());
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
            $this->dispatch('load-tree', tree: null);
        }
    }

    public function resetSelection()
    {
        $this->selectedMemberId = '';
        $this->searchQuery = '';
        $this->filteredMembers = $this->allMembers;
        $this->selectedMemberDetails = null;
        $this->dispatch('load-tree', tree: null);
        session()->flash('message', 'Selection reset.');
    }

    public function showMemberDetails($id)
    {
        $member = Genealogy::find($id);
        if ($member) {
            $this->selectedMemberDetails = [
                'name' => $member->name,
                'pustanumber' => $person->pustanumber,
                'sex' => $member->sex,
                'image' => $member->image && Storage::exists($member->image) ? Storage::url($member->image) : null,
                'description' => $member->description ?? 'N/A',
                'date_of_birth' => $member->date_of_birth ? $member->date_of_birth->format('F j, Y') : 'N/A',
                'date_of_death' => $member->date_of_death ? $member->date_of_death->format('F j, Y') : 'N/A',
                'mother_name' => $member->mother_name ?? 'N/A',
                'wife_name' => $member->wife_name ?? 'N/A',
            ];
            $this->dispatch('open-member-modal');
        }
    }

    private function buildTree($person)
    {
        $node = [
            'id' => $person->id,
            'text' => $person->name, // Use 'text' for jstree compatibility
            'pustanumber' => $person->pustanumber,
            'sex' => $person->sex,
            'image' => $person->image && Storage::exists($person->image) ? Storage::url($person->image) : null,
            'description' => $person->description ?? 'N/A',
            'date_of_birth' => $person->date_of_birth ? $person->date_of_birth->format('F j, Y') : 'N/A',
            'date_of_death' => $person->date_of_death ? $person->date_of_death->format('F j, Y') : 'N/A',
            'mother_name' => $person->mother_name ?? 'N/A',
            'wife_name' => $person->wife_name ?? 'N/A',
            'children' => [],
        ];

        foreach ($person->children as $child) {
            $node['children'][] = $this->buildTree($child);
        }

        return $node;
    }

    public function render()
    {
        return view('livewire.frontend.genealogy-tree')->layout('components.layouts.frontend');
    }
}