<?php

namespace App\Livewire\Admin\Genealogys;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Genealogy;

class Create extends Component
{
    use WithFileUploads;

    public $pustanumber, $name, $sex = 'male', $date_of_birth, $date_of_death;
    public $description, $father_id, $mother_name, $wife_name;
    public $image;

    protected $rules = [
        'pustanumber'   => 'required|integer|min:0',
        'name'          => 'required|string|max:255',
        'sex'           => 'required|in:male,female,other',
        'date_of_birth' => 'nullable|date|before_or_equal:today',
        'date_of_death' => 'nullable|date|after_or_equal:date_of_birth|before_or_equal:today',
        'description'   => 'nullable|string|max:1000',
        'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        'father_id'     => 'nullable|exists:genealogies,id',
        'mother_name'   => 'nullable|string|max:255',
        'wife_name'     => 'nullable|string|max:255',
    ];

    protected $messages = [
        'pustanumber.integer' => 'The pusta number must be a valid integer.',
        'pustanumber.min' => 'The pusta number cannot be negative.',
        'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
        'date_of_death.after_or_equal' => 'Date of death cannot be before date of birth.',
        'date_of_death.before_or_equal' => 'Date of death cannot be in the future.',
        'image.mimes' => 'The image must be a JPEG, PNG, JPG, or GIF file.',
        'image.max' => 'The image size must not exceed 4MB.',
    ];

    public function searchFathers($query)
    {
        if (strlen($query) < 2) {
            return ['results' => [], 'hasMore' => false];
        }
        
        $results = Genealogy::where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('pustanumber', 'like', '%' . $query . '%');
            })
            ->orderBy('pustanumber', 'desc')
            ->limit(20)
            ->get(['id', 'name', 'pustanumber']);
            
        return [
            'results' => $results,
            'hasMore' => $results->count() >= 20
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        try {
            $imagePath = $this->image
                ? $this->image->store('media/' . now()->format('Y/m'), 'public')
                : null;

            Genealogy::create([
                'pustanumber'   => $validated['pustanumber'],
                'name'          => trim($validated['name']),
                'sex'           => $validated['sex'],
                'date_of_birth' => $validated['date_of_birth'],
                'date_of_death' => $validated['date_of_death'],
                'description'   => trim($validated['description'] ?? ''),
                'image'         => $imagePath,
                'father_id'     => $this->father_id,
                'mother_name'   => trim($validated['mother_name'] ?? ''),
                'wife_name'     => trim($validated['wife_name'] ?? ''),
            ]);

            session()->flash('message', 'Member created successfully.');

            $this->resetForm();

            return redirect()->to('/admin/genealogys/create');
        } catch (\Exception $e) {
            \Log::error('Genealogy Create Error: ' . $e->getMessage());
            session()->flash('error', 'Failed to create member. Please try again.');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'pustanumber', 'name', 'sex', 'date_of_birth', 'date_of_death',
            'description', 'father_id', 'mother_name', 'wife_name', 'image',
        ]);

        $this->sex = 'male';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.genealogys.create', [
            'allMembers' => Genealogy::orderBy('pustanumber', 'desc')->get()
        ]);
    }
}