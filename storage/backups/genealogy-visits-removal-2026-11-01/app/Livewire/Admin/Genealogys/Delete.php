<?php

namespace App\Livewire\Admin\Genealogys;

use Livewire\Component;
use App\Models\Genealogy;
use Illuminate\Support\Facades\Storage;

class Delete extends Component
{
    public $memberId;

    public function mount($id)
    {
        $this->memberId = $id;
    }

    public function delete()
    {
        try {
            $member = Genealogy::findOrFail($this->memberId);

            // Check if member is referenced as a father
            if (Genealogy::where('father_id', $this->memberId)->exists()) {
                session()->flash('error', 'Cannot delete member: They are referenced as a father.');
                return;
            }

            // Delete associated image if exists
            if ($member->image) {
                Storage::disk('public')->delete($member->image);
            }

            // Delete the member
            $member->delete();

            // Flash success message and emit event
            session()->flash('message', 'Member deleted successfully.');
            $this->dispatch('genealogyDeleted');

            // Redirect to main manager
            $this->redirect(route('genealogy.manager'));
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete member: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $member = Genealogy::findOrFail($this->memberId);
        return view('livewire.admin.genealogys.delete', [
            'member' => $member,
        ]);
    }
}