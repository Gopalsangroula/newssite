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
    public string $sortBy = 'pustanumber';
    public string $sortDirection = 'asc';
    public string $birthDateFrom = '';
    public string $birthDateTo = '';
    public int $perPage = 10;
    public int $rootsPerPage = 2;
    public string $homeUrl = '';
    public int $searchPerPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'genderFilter' => ['except' => ''],
        'sortBy' => ['except' => 'pustanumber'],
        'sortDirection' => ['except' => 'asc'],
        'birthDateFrom' => ['except' => ''],
        'birthDateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->homeUrl = Route::has('frontend.welcome')
            ? route('frontend.welcome')
            : url('/');
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'genderFilter', 'sortBy', 'sortDirection', 'birthDateFrom', 'birthDateTo', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->genderFilter = '';
        $this->sortBy = 'pustanumber';
        $this->sortDirection = 'asc';
        $this->birthDateFrom = '';
        $this->birthDateTo = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function getTotalCountProperty()
    {
        return Genealogy::count();
    }

    public function getMaleCountProperty()
    {
        return Genealogy::male()->count();
    }

    public function getFemaleCountProperty()
    {
        return Genealogy::female()->count();
    }

    public function getLivingCountProperty()
    {
        return Genealogy::living()->count();
    }

    public function getFamilyBranchesProperty()
    {
        return Genealogy::whereNull('father_id')->count();
    }

    public function getActiveFilterCountProperty()
    {
        $count = 0;
        if ($this->genderFilter) $count++;
        if ($this->sortBy !== 'pustanumber') $count++;
        if ($this->birthDateFrom) $count++;
        if ($this->birthDateTo) $count++;
        if ($this->search) $count++;
        return $count;
    }

    public function exportToPdf()
    {
        session()->flash('message', 'PDF export feature will be implemented soon!');
    }

    private function buildMatchingQuery()
    {
        $query = Genealogy::query();

        if ($this->search) {
            $searchTerms = array_filter(explode(' ', trim($this->search)));
            
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQ) use ($term) {
                        $subQ->where('name', 'LIKE', '%' . $term . '%')
                            ->orWhere('pustanumber', 'LIKE', '%' . $term . '%')
                            ->orWhere('wife_name', 'LIKE', '%' . $term . '%');
                    });
                }
            });
        }

        if ($this->genderFilter) {
            $query->where('sex', $this->genderFilter);
        }

        if ($this->birthDateFrom) {
            $query->whereDate('date_of_birth', '>=', $this->birthDateFrom);
        }

        if ($this->birthDateTo) {
            $query->whereDate('date_of_birth', '<=', $this->birthDateTo);
        }

        return $query;
    }

    private function getRelevantRootIds(): ?array
    {
        $hasAnyFilter = $this->search || $this->genderFilter || $this->birthDateFrom || $this->birthDateTo;

        if (! $hasAnyFilter) {
            return null;
        }

        $matches = $this->buildMatchingQuery()->get(['id', 'father_id']);

        if ($matches->isEmpty()) {
            return [];
        }

        $parentCache = [];
        $rootIds = [];

        foreach ($matches as $m) {
            $current = $m;
            while ($current->father_id) {
                $pid = $current->father_id;
                if (array_key_exists($pid, $parentCache)) {
                    $parent = $parentCache[$pid];
                } else {
                    $parent = Genealogy::select('id', 'father_id')->find($pid);
                    $parentCache[$pid] = $parent;
                }
                if (! $parent) {
                    break;
                }
                $current = $parent;
            }
            if ($current && $current->id) {
                $rootIds[] = $current->id;
            }
        }

        return array_values(array_unique($rootIds));
    }

    private function getSubtreeForRootIds(array $rootIds): Collection
    {
        if (empty($rootIds)) {
            return collect();
        }

        $all = Genealogy::whereIn('id', $rootIds)->get()->keyBy('id');
        $currentIds = $rootIds;

        while (! empty($currentIds)) {
            $children = Genealogy::whereIn('father_id', $currentIds)->get();
            if ($children->isEmpty()) break;

            foreach ($children as $child) {
                if (! isset($all[$child->id])) {
                    $all[$child->id] = $child;
                }
            }

            $currentIds = $children->pluck('id')->all();
        }

        return collect($all)->sortBy(function ($item) {
            return $item->pustanumber ?? $item->id;
        })->values();
    }

    public function render()
    {
        $hasSearch = !empty($this->search);
        $relevantRootIds = $this->getRelevantRootIds();

        // If search exists, show grid results
        if ($hasSearch) {
            $searchResults = $this->buildMatchingQuery()
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate($this->searchPerPage)
                ->withQueryString();

            return view('livewire.frontend.genealogy-archive', [
                'roots' => collect(),
                'people' => collect(),
                'searchResults' => $searchResults,
                'filteredCount' => $this->buildMatchingQuery()->count(),
                'breadcrumbs' => $this->breadcrumbs,
                'totalCount' => $this->totalCount,
                'maleCount' => $this->maleCount,
                'femaleCount' => $this->femaleCount,
                'livingCount' => $this->livingCount,
                'familyBranches' => $this->familyBranches,
                'activeFilterCount' => $this->activeFilterCount,
                'hasSearch' => $hasSearch,
            ])->layout('components.layouts.frontend', [
                'title' => 'वंशावली - ' . config('app.name'),
                'metaDescription' => 'परिवार की वंशावली देखें और खोजें। सभी सदस्यों की जानकारी एक ही स्थान पर।',
                'breadcrumbs' => $this->breadcrumbs,
            ]);
        }

        // Tree view (no search)
        if (is_null($relevantRootIds)) {
            $rootsQuery = Genealogy::whereNull('father_id')
                ->orderBy($this->sortBy, $this->sortDirection);
        } else {
            if (empty($relevantRootIds)) {
                $rootsQuery = Genealogy::whereRaw('0 = 1');
            } else {
                $rootsQuery = Genealogy::whereIn('id', $relevantRootIds)
                    ->orderBy($this->sortBy, $this->sortDirection);
            }
        }

        $roots = $rootsQuery->paginate($this->rootsPerPage)->withQueryString();

        $rootIdsOnPage = $roots->pluck('id')->all();
        $people = $this->getSubtreeForRootIds($rootIdsOnPage);

        return view('livewire.frontend.genealogy-archive', [
            'roots' => $roots,
            'people' => $people,
            'searchResults' => null,
            'filteredCount' => $this->buildMatchingQuery()->count(),
            'breadcrumbs' => $this->breadcrumbs,
            'totalCount' => $this->totalCount,
            'maleCount' => $this->maleCount,
            'femaleCount' => $this->femaleCount,
            'livingCount' => $this->livingCount,
            'familyBranches' => $this->familyBranches,
            'activeFilterCount' => $this->activeFilterCount,
            'hasSearch' => $hasSearch,
        ])->layout('components.layouts.frontend', [
            'title' => 'वंशावली - ' . config('app.name'),
            'metaDescription' => 'परिवार की वंशावली देखें और खोजें। सभी सदस्यों की जानकारी एक ही स्थान पर।',
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function getBreadcrumbsProperty()
    {
        return [
            ['name' => 'Home', 'url' => $this->homeUrl],
            ['name' => 'वंशावली', 'url' => null],
        ];
    }

    /**
     * Helper function to build tree node HTML
     */
    public function buildTreeNode($person, $collection, $level = 0)
        {
            $children = $collection->where('father_id', $person->id)->sortBy('date_of_birth');

            $imageSize = $this->isMobile() ? '100' : '200';
            $imageUrl = $person->image
                ? asset('storage/' . $person->image)
                : 'https://ui-avatars.com/api/?name=' . urlencode($person->name) . '&background=1e87f0&color=fff&bold=true&size=' . $imageSize;

            $dob = $person->date_of_birth?->format('d M Y') ?? '—';
            $dod = $person->date_of_death?->format('d M Y');
            $dates = $dod ? "$dob – $dod" : $dob;
            $age = $person->date_of_birth ? now()->diffInYears($person->date_of_birth) : null;
            $lifespan = $age ? "($age years)" : '';

            $hasChildren = $children->isNotEmpty();
            $cardClass = $hasChildren ? 'has-children' : 'no-children';

            $html = '<li data-level="' . $level . '" data-person-id="' . $person->id . '" data-pustanumber="' . e($person->pustanumber) . '" class="tree-level-' . $level . '">';
            $html .= '<div class="node-container">';
            
            // CORRECT: Use ID for the route
            $html .= '<a href="' . route('frontend.genealogy.show', $person->id) . '" class="person-card ' . $cardClass . '">';
            
            $html .= '<div class="card-image">';
            $html .= '<img src="' . $imageUrl . '" alt="' . e($person->name) . '" loading="lazy" class="uk-border-circle">';
            if ($hasChildren) {
                $html .= '<div class="children-indicator uk-visible@m" uk-tooltip="' . $children->count() . ' children">';
                $html .= '<span uk-icon="users"></span>';
                $html .= '</div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="card-content">';
            $html .= '<div class="uk-card-title">' . e($person->name) . '</div>';
            $html .= '<div class="pustanumber uk-text-small uk-text-muted uk-text-tiny@s">पु.नं: ' . e($person->pustanumber) . '</div>';
            $html .= '<div class="dates uk-text-small uk-text-muted uk-text-tiny@s">' . $dates . ' <span class="lifespan">' . $lifespan . '</span></div>';
            if ($person->wife_name) {
                $html .= '<div class="spouse uk-text-small uk-text-muted uk-text-tiny@s uk-visible@m">';
                $html .= '<span uk-icon="heart"></span> ' . e($person->wife_name);
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '</a>';

            if ($hasChildren) {
                $html .= '<button class="toggle-children uk-button uk-button-small uk-button-default uk-button-tiny@s" uk-tooltip="Expand children" aria-expanded="false">';
                $html .= '<span class="toggle-icon" uk-icon="chevron-down"></span>';
                $html .= '</button>';
            }
            $html .= '</div>';

            if ($hasChildren) {
                $html .= '<ul class="children-level level-' . ($level + 1) . ' uk-overflow-fade collapsed">';
                foreach ($children as $child) {
                    $html .= $this->buildTreeNode($child, $collection, $level + 1);
                }
                $html .= '</ul>';
            }

            $html .= '</li>';
            return $html;
        }

    /**
     * Helper function to check if device is mobile
     */
    private function isMobile(): bool
    {
        return preg_match("/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i", $_SERVER['HTTP_USER_AGENT'] ?? '');
    }
}