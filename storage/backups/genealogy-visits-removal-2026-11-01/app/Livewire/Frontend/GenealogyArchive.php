<?php

namespace App\Livewire\Frontend;

use App\Models\Genealogy;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;

class GenealogyArchive extends Component
{
    use WithPagination;

    // Public properties
    public string $search = '';
    public string $genderFilter = '';
    public string $generationFilter = '';
    public string $sortBy = 'pustanumber';
    public string $sortDirection = 'asc';
    public string $birthDateFrom = '';
    public string $birthDateTo = '';
    public int $perPage = 10;
    public int $rootsPerPage = 2;
    public string $homeUrl = '';
    public int $searchPerPage = 12;

    // 'tree' or 'list'. Any active filter shows the list whatever this says.
    public string $mode = 'tree';

    // Changing this rebuilds the filter fields. The Alpine CDN script in the footer stops
    // wire:model pushing a server-side reset back into the fields, so they would keep the old text.
    public int $filterKey = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'genderFilter' => ['except' => ''],
        'generationFilter' => ['except' => ''],
        'sortBy' => ['except' => 'pustanumber'],
        'sortDirection' => ['except' => 'asc'],
        'birthDateFrom' => ['except' => ''],
        'birthDateTo' => ['except' => ''],
        'mode' => ['except' => 'tree'],
    ];

    public function mount()
    {
        $this->homeUrl = Route::has('frontend.welcome')
            ? route('frontend.welcome')
            : url('/');
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'genderFilter', 'generationFilter', 'sortBy', 'sortDirection', 'birthDateFrom', 'birthDateTo', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->genderFilter = '';
        $this->generationFilter = '';
        $this->sortBy = 'pustanumber';
        $this->sortDirection = 'asc';
        $this->birthDateFrom = '';
        $this->birthDateTo = '';
        $this->perPage = 10;
        $this->filterKey++;
        $this->resetPage();
    }

    public function showTree()
    {
        $this->mode = 'tree';
        $this->resetFilters();
    }

    public function showList()
    {
        $this->mode = 'list';
        $this->resetPage();
    }

    public function getActiveFilterCountProperty()
    {
        $count = 0;
        if ($this->genderFilter) $count++;
        if ($this->generationFilter !== '') $count++;
        if ($this->sortBy !== 'pustanumber') $count++;
        if ($this->birthDateFrom) $count++;
        if ($this->birthDateTo) $count++;
        if (trim($this->search) !== '') $count++;
        return $count;
    }

    public function exportToPdf()
    {
        session()->flash('message', 'PDF export feature will be implemented soon!');
    }

    private function buildMatchingQuery()
    {
        $query = Genealogy::query();

        if (trim($this->search) !== '') {
            $searchTerms = array_filter(explode(' ', trim($this->search)), 'strlen');

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQ) use ($term) {
                        // Generation numbers are stored in Latin digits; a Nepali keyboard types ४४.
                        $number = strtr($term, ['०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4', '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9']);

                        $subQ->where('name', 'LIKE', '%' . $term . '%')
                            ->orWhere('pustanumber', 'LIKE', '%' . $number . '%')
                            ->orWhere('wife_name', 'LIKE', '%' . $term . '%');
                    });
                }
            });
        }

        if ($this->genderFilter) {
            $query->where('sex', $this->genderFilter);
        }

        if ($this->generationFilter !== '') {
            $query->where('pustanumber', $this->generationFilter);
        }

        if ($this->birthDateFrom) {
            $query->whereDate('date_of_birth', '>=', $this->birthDateFrom);
        }

        if ($this->birthDateTo) {
            $query->whereDate('date_of_birth', '<=', $this->birthDateTo);
        }

        return $query;
    }

    /**
     * Sort order from the URL, limited to known columns. The id keeps pages stable
     * when many members share a generation.
     */
    private function applySort($query)
    {
        $column = in_array($this->sortBy, ['pustanumber', 'name', 'date_of_birth'], true) ? $this->sortBy : 'pustanumber';
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($column, $direction)->orderBy('id');
    }

    /**
     * Each member's children keyed by father. One query for the whole family
     * instead of one per generation.
     */
    private function childrenByFather(): Collection
    {
        return Genealogy::query()
            ->whereNotNull('father_id')
            ->orderBy('date_of_birth')
            ->orderBy('id')
            ->get(['id', 'father_id', 'name', 'sex', 'image', 'pustanumber'])
            ->groupBy('father_id');
    }

    public function render()
    {
        $showList = $this->mode === 'list' || $this->activeFilterCount > 0;

        // Members per generation, which also gives the page totals.
        $generations = Genealogy::query()
            ->selectRaw('pustanumber, COUNT(*) AS members')
            ->groupBy('pustanumber')
            ->orderBy('pustanumber')
            ->toBase()
            ->pluck('members', 'pustanumber');

        $data = [
            'showList' => $showList,
            'members' => null,
            'roots' => collect(),
            'childrenByFather' => collect(),
            'generations' => $generations,
            'totalCount' => $generations->sum(),
            'generationCount' => $generations->keys()->max() ?? 0,
            'breadcrumbs' => $this->breadcrumbs,
            'activeFilterCount' => $this->activeFilterCount,
        ];

        if ($showList) {
            $data['members'] = $this->applySort($this->buildMatchingQuery())
                ->with('father:id,name')
                ->paginate($this->searchPerPage)
                ->appends(array_filter([
                    'search' => trim($this->search),
                    'genderFilter' => $this->genderFilter,
                    'generationFilter' => $this->generationFilter,
                    'sortBy' => $this->sortBy !== 'pustanumber' ? $this->sortBy : '',
                    'mode' => $this->mode !== 'tree' ? $this->mode : '',
                ], 'strlen'));
        } else {
            $data['roots'] = $this->applySort(Genealogy::whereNull('father_id'))->paginate($this->rootsPerPage);
            $data['childrenByFather'] = $this->childrenByFather();
        }

        return view('livewire.frontend.genealogy-archive', $data)->layout('components.layouts.frontend', [
            'title' => 'वंशावली - ' . config('app.name'),
            'metaDescription' => 'लिबाङ वंशावली हेर्नुहोस् र खोज्नुहोस्। सबै सदस्यको जानकारी एकै ठाउँमा।',
            'breadcrumbs' => $data['breadcrumbs'],
        ]);
    }

    public function getBreadcrumbsProperty()
    {
        return [
            ['name' => 'गृहपृष्ठ', 'url' => $this->homeUrl],
            ['name' => 'वंशावली', 'url' => null],
        ];
    }
}
