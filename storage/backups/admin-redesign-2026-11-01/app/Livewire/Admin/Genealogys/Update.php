<?php

namespace App\Livewire\Admin\Genealogys;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Genealogy;
use Illuminate\Support\Facades\Storage;

class Update extends Component
{
    use WithFileUploads;

    public $editingId, $pustanumber, $name, $sex, $date_of_birth, $date_of_death, $description, $father_id, $mother_name, $wife_name;
    public $image, $imagePreview;

    protected function rules()
    {
        return [
            'pustanumber' => 'required|integer|min:0',
            'name' => 'required|string|max:255',
            'sex' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'date_of_death' => 'nullable|date|after_or_equal:date_of_birth|before_or_equal:today',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'father_id' => 'nullable|exists:genealogies,id',
            'mother_name' => 'nullable|string|max:255',
            'wife_name' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'pustanumber.integer' => 'The pusta number must be a valid integer.',
        'pustanumber.min' => 'The pusta number cannot be negative.',
        'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
        'date_of_death.after_or_equal' => 'Date of death cannot be before date of birth.',
        'date_of_death.before_or_equal' => 'Date of death cannot be in the future.',
        'image.mimes' => 'The image must be a JPEG, PNG, JPG, or GIF file.',
        'image.max' => 'The image size must not exceed 4MB.',
    ];

    // Listen to Choices.js event
    protected $listeners = [
        'fatherUpdated' => 'updateFather'
    ];

    public function updateFather($data)
    {
        $this->father_id = $data['value'] ?: null;
    }

    public function mount($id)
    {
        $member = Genealogy::find($id);
        if (!$member) {
            session()->flash('error', 'Member not found.');
            return;
        }

        $this->editingId = $member->id;
        $this->pustanumber = $member->pustanumber;
        $this->name = $member->name;
        $this->sex = $member->sex;
        $this->date_of_birth = $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : null;
        $this->date_of_death = $member->date_of_death ? $member->date_of_death->format('Y-m-d') : null;
        $this->description = $member->description;
        $this->father_id = $member->father_id;
        $this->mother_name = $member->mother_name;
        $this->wife_name = $member->wife_name;
        $this->imagePreview = $member->image ? Storage::url($member->image) : null;
    }

    public function update()
    {
        $validated = $this->validate();

        try {
            $member = Genealogy::findOrFail($this->editingId);

            // Handle image upload
            $path = $member->image;
            if ($this->image) {
                // Delete old image if exists
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                // Store new image
                $path = $this->image->store('media/' . now()->format('Y/m'), 'public');
            }

            // Prepare data for update
            $data = [
                'pustanumber' => $validated['pustanumber'], // No trimming for integer
                'name' => trim($validated['name']),
                'sex' => $validated['sex'],
                'date_of_birth' => $validated['date_of_birth'],
                'date_of_death' => $validated['date_of_death'],
                'description' => trim($validated['description'] ?? ''),
                'image' => $path,
                'father_id' => $validated['father_id'] ?: null,
                'mother_name' => trim($validated['mother_name'] ?? ''),
                'wife_name' => trim($validated['wife_name'] ?? ''),
            ];

            // Update genealogy record
            $member->update($data);

            // Flash success message and emit event
            session()->flash('message', 'Member updated successfully.');
            $this->dispatch('genealogyUpdated');

            // Redirect to genealogy manager
            return redirect()->route('admin.genealogys.index');
        } catch (\Exception $e) {
            \Log::error('Failed to update genealogy member: ' . $e->getMessage());
            session()->flash('error', 'Failed to update member. Please try again.');
        }
    }

    public function removeImage()
    {
        $member = Genealogy::findOrFail($this->editingId);
        if ($member->image && Storage::disk('public')->exists($member->image)) {
            Storage::disk('public')->delete($member->image);
            $member->update(['image' => null]);
        }
        $this->image = null;
        $this->imagePreview = null;
    }

    public function render()
    {
        // Load all members for parent dropdowns, excluding the current member
        $allMembers = Genealogy::where('id', '!=', $this->editingId)->orderBy('name')->get();

        return view('livewire.admin.genealogys.update', [
            'allMembers' => $allMembers,
        ]);
    }
}