<?php

namespace App\Livewire\Admin\Genealogys\Concerns;

use App\Models\Genealogy;
use Livewire\Attributes\Renderless;

trait SearchesFathers
{
    /**
     * Find members for the father picker, by name or pusta number.
     * Results are plain arrays: serializing the model runs a query per row.
     */
    #[Renderless]
    public function searchFathers($query)
    {
        $query = trim((string) $query);

        if (strlen($query) < 2) {
            return ['results' => [], 'hasMore' => false];
        }

        $results = Genealogy::with('father:id,name')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('pustanumber', 'like', '%' . $query . '%');
            })
            // A member cannot be their own father
            ->when($this->editingId ?? null, fn ($q, $id) => $q->whereKeyNot($id))
            ->orderBy('pustanumber', 'desc')
            ->limit(20)
            ->get(['id', 'name', 'pustanumber', 'father_id']);

        return [
            'results' => $results->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'pustanumber' => $member->pustanumber,
                'father' => $member->father_id ? $member->father->name : null,
            ])->all(),
            'hasMore' => $results->count() >= 20,
        ];
    }
}
