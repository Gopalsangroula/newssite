<?php

namespace App\Livewire\Admin;

use App\Models\MetalRate;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Gold & Silver Rates')]
class MetalRates extends Component
{
    use WithPagination;

    public $rate_date = '';
    public $fine_gold = '';
    public $tejabi_gold = '';
    public $silver = '';

    protected $validationAttributes = [
        'rate_date' => 'date',
        'fine_gold' => 'fine gold rate',
        'tejabi_gold' => 'tejabi gold rate',
        'silver' => 'silver rate',
    ];

    public function mount()
    {
        $this->rate_date = today()->toDateString();

        // Rates move a little each day, so yesterday's figures are the quickest place to start.
        $this->fillRates(MetalRate::latest('rate_date')->first());
    }

    protected function rules()
    {
        return [
            'rate_date' => 'required|date|before_or_equal:today',
            'fine_gold' => 'required|integer|min:1|max:99999999',
            'tejabi_gold' => 'required|integer|min:1|max:99999999',
            'silver' => 'required|integer|min:1|max:99999999',
        ];
    }

    /**
     * Choosing a day that already has rates loads them, so that saving corrects that day.
     */
    public function updatedRateDate()
    {
        $saved = MetalRate::whereDate('rate_date', $this->rate_date)->first();

        if ($saved) {
            $this->fillRates($saved);
        }
    }

    public function edit($id)
    {
        $rate = MetalRate::findOrFail($id);

        $this->rate_date = $rate->rate_date->toDateString();
        $this->fillRates($rate);
        $this->resetValidation();
    }

    public function save()
    {
        $validated = $this->validate();

        MetalRate::updateOrCreate(
            ['rate_date' => $validated['rate_date']],
            collect($validated)->only(array_keys(MetalRate::METALS))->all()
        );

        session()->flash('message', 'Rates saved for '.$this->rate_date.'.');
    }

    public function delete($id)
    {
        MetalRate::findOrFail($id)->delete();

        session()->flash('message', 'Rates deleted.');
    }

    private function fillRates(?MetalRate $rate): void
    {
        foreach (array_keys(MetalRate::METALS) as $metal) {
            $this->{$metal} = $rate?->{$metal} ?? '';
        }
    }

    public function render()
    {
        return view('livewire.admin.metal-rates', [
            'rates' => MetalRate::latest('rate_date')->paginate(10),
        ]);
    }
}
