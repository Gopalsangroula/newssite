<?php

namespace App\Livewire\Admin;

use App\Models\Rashifal;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Rashifal')]
class Rashifals extends Component
{
    use WithPagination;

    public $rashifal_date = '';
    public $author = '';
    public $readings = [];

    public function mount()
    {
        $this->rashifal_date = today()->toDateString();
        $this->loadDay();
    }

    protected function rules()
    {
        return [
            'rashifal_date' => 'required|date',
            'author' => 'nullable|string|max:120',
            'readings.*' => 'required|string|max:800',
        ];
    }

    protected function messages()
    {
        return [
            'readings.*.required' => 'Please write this sign\'s reading.',
            'readings.*.max' => 'Please keep this reading under 800 characters.',
        ];
    }

    /**
     * Choosing a day that already has a rashifal loads it, so that saving corrects that day.
     */
    public function updatedRashifalDate()
    {
        $this->loadDay();
        $this->resetValidation();
    }

    public function edit($id)
    {
        $this->rashifal_date = Rashifal::findOrFail($id)->rashifal_date->toDateString();
        $this->loadDay();
        $this->resetValidation();
    }

    public function save()
    {
        $this->readings = array_map('trim', $this->readings);
        $validated = $this->validate();

        Rashifal::updateOrCreate(
            ['rashifal_date' => $validated['rashifal_date']],
            ['author' => trim((string) $validated['author']) ?: null, 'readings' => $this->readings]
        );

        session()->flash('message', 'Rashifal saved for '.$this->rashifal_date.'.');
    }

    public function delete($id)
    {
        Rashifal::findOrFail($id)->delete();
        $this->loadDay();

        session()->flash('message', 'Rashifal deleted.');
    }

    private function loadDay(): void
    {
        $saved = Rashifal::whereDate('rashifal_date', $this->rashifal_date)->first();

        $this->author = $saved->author ?? '';
        $this->readings = collect(Rashifal::SIGNS)
            ->map(fn ($sign, $key) => $saved->readings[$key] ?? '')
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.rashifals', [
            'days' => Rashifal::latest('rashifal_date')->paginate(10),
        ]);
    }
}
