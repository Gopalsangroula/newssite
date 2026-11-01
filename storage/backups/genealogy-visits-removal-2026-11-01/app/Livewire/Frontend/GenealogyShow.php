<?php

namespace App\Livewire\Frontend;

use App\Models\Genealogy;
use Livewire\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GenealogyShow extends Component
{
    public Genealogy $genealogy;
    public string $title = '';
    public string $metaDescription = '';
    public string $homeUrl = '';
    public string $genealogyIndexUrl = '';
    public array $activeTabs = ['basic' => true, 'family' => false, 'details' => false];

    public function mount(Genealogy $genealogy)
    {
        // This will use ID by default (route model binding)
        $this->genealogy = $genealogy->load([
            'father',
            'children' => fn($q) => $q->orderBy('date_of_birth')->orderBy('id'),
        ]);

        $this->homeUrl = Route::has('frontend.welcome')
            ? route('frontend.welcome')
            : url('/');

        $this->genealogyIndexUrl = Route::has('frontend.genealogy.index')
            ? route('frontend.genealogy.index')
            : url('/genealogy');

        $this->setPageMetadata();
    }

    protected function setPageMetadata()
    {
        $name = $this->genealogy->name;

        if ($this->genealogy->sex === 'male' && $this->genealogy->wife_name) {
            $name .= " (पत्नी: {$this->genealogy->wife_name})";
        }

        $this->title = "$name - वंशावली | " . config('app.name');

        $desc = "पुस्तक नं: {$this->genealogy->pustanumber}, नाम: {$this->genealogy->name}";
        
        if ($this->genealogy->date_of_birth) {
            $desc .= ", जन्म: " . $this->genealogy->date_of_birth->format('j F Y');
        }
        
        if ($this->genealogy->wife_name && $this->genealogy->sex === 'male') {
            $desc .= ", पत्नी: {$this->genealogy->wife_name}";
        }

        $this->metaDescription = Str::limit($desc, 160);
    }

    public function activateTab($tabName)
    {
        $this->activeTabs = array_fill_keys(array_keys($this->activeTabs), false);
        $this->activeTabs[$tabName] = true;
    }

    public function getBreadcrumbsProperty()
    {
        $crumbs = [
            ['name' => 'गृहपृष्ठ', 'url' => $this->homeUrl],
            ['name' => 'वंशावली', 'url' => $this->genealogyIndexUrl],
        ];

        $person = $this->genealogy;
        $chain = [];
        $visitedIds = [$person->id]; 

        while ($person && $person->father_id && $person->father && count($chain) < 4) {
            if (in_array($person->father_id, $visitedIds)) {
                break;
            }

            $visitedIds[] = $person->father_id;
            $chain[] = $person->father;
            $person = $person->father;
        }
        foreach (array_reverse($chain) as $ancestor) {
            if ($ancestor && !empty($ancestor->id)) {
                $crumbs[] = [
                    'name' => $ancestor->name,
                    'url'  => route('frontend.genealogy.show', ['genealogy' => $ancestor->id])
                ];
            }
        }

        $crumbs[] = ['name' => $this->genealogy->name, 'url' => null];

        return $crumbs;
    }

    public function render()
    {
        $breadcrumbs = $this->breadcrumbs;

        return view('livewire.frontend.genealogy-show', [
            'breadcrumbs' => $breadcrumbs,
        ])->layout('components.layouts.frontend', [
            'title'           => $this->title,
            'metaDescription' => $this->metaDescription,
            'breadcrumbs'     => $breadcrumbs,
        ]);
    }
}