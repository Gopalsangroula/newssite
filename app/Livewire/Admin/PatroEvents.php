<?php

namespace App\Livewire\Admin;

use App\Models\PatroEvent;
use App\Support\HolidayFeed;
use App\Support\NepaliDate;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Patro Events')]
class PatroEvents extends Component
{
    // The Nepali month on screen.
    public $year;
    public $month;

    public $editingId = null;
    public $day = 1;
    public $name = '';
    public $is_holiday = false;

    public function mount()
    {
        $today = NepaliDate::parts(now());

        $this->year = $today['year'];
        $this->month = $today['month'];
        $this->day = $today['day'];
    }

    protected function rules()
    {
        return [
            'day' => 'required|integer|min:1|max:'.max(1, NepaliDate::monthLength((int) $this->year, (int) $this->month)),
            'name' => 'required|string|max:120',
            'is_holiday' => 'boolean',
        ];
    }

    public function updatedYear()
    {
        $this->startNew();
    }

    public function updatedMonth()
    {
        $this->startNew();
    }

    public function save()
    {
        $validated = $this->validate();

        $values = [
            'event_date' => NepaliDate::toGregorian((int) $this->year, (int) $this->month, (int) $validated['day'])->toDateString(),
            'name' => trim($validated['name']),
            'is_holiday' => (bool) $validated['is_holiday'],
        ];

        if ($this->editingId) {
            PatroEvent::findOrFail($this->editingId)->update($values);
        } else {
            PatroEvent::create($values);
        }

        session()->flash('message', 'Event saved.');
        $this->startNew();
    }

    public function edit($id)
    {
        $event = PatroEvent::findOrFail($id);
        $parts = NepaliDate::parts($event->event_date);

        $this->editingId = $event->id;
        $this->year = $parts['year'];
        $this->month = $parts['month'];
        $this->day = $parts['day'];
        $this->name = $event->name;
        $this->is_holiday = $event->is_holiday;
        $this->resetValidation();
    }

    public function startNew()
    {
        $this->reset('editingId', 'name', 'is_holiday');
        $this->day = 1;
        $this->resetValidation();
    }

    public function delete($id)
    {
        PatroEvent::findOrFail($id)->delete();

        if ((int) $this->editingId === (int) $id) {
            $this->startNew();
        }

        session()->flash('message', 'Event removed from the patro.');
    }

    public function importCalendar()
    {
        try {
            $added = HolidayFeed::import();
            session()->flash('message', $added.' new '.($added === 1 ? 'event' : 'events').' added from the public calendar.');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'The public calendar could not be reached. Please try again later.');
        }
    }

    public function render()
    {
        $length = NepaliDate::monthLength((int) $this->year, (int) $this->month);
        $firstDay = NepaliDate::toGregorian((int) $this->year, (int) $this->month, 1);

        return view('livewire.admin.patro-events', [
            'length' => $length,
            'events' => PatroEvent::whereBetween('event_date', [$firstDay->toDateString(), $firstDay->copy()->addDays($length - 1)->toDateString()])
                ->orderBy('event_date')
                ->orderByDesc('is_holiday')
                ->get(),
        ]);
    }
}
