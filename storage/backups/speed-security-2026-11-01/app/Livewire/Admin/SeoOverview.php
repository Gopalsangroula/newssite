<?php

namespace App\Livewire\Admin;

use App\Models\Page;
use App\Models\Post;
use App\Support\Seo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('SEO')]
class SeoOverview extends Component
{
    use WithPagination;

    public $type = 'posts';
    public $search = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingType() { $this->resetPage(); }

    public function render()
    {
        $canSeePages = Auth::user()->can('access pages');
        $showPages = $canSeePages && $this->type === 'pages';

        $query = ($showPages ? Page::query() : Post::query())
            ->when(Auth::user()->hasRole('editor'), fn ($q) => $q->where('created_by', Auth::id()));

        $summary = [
            'No meta description' => (clone $query)->whereNull('seo_description')->count(),
            'No focus keyword' => (clone $query)->whereNull('focus_keyword')->count(),
            'No image' => (clone $query)->whereNull('cover_image')->whereNull('og_image')->count(),
            'Hidden from search' => (clone $query)->where('noindex', true)->count(),
        ];

        $items = $query
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.seo.overview', [
            'items' => $items,
            'summary' => $summary,
            'showPages' => $showPages,
            'canSeePages' => $canSeePages,
            'checkLabels' => [
                'title' => 'title length',
                'description' => 'description',
                'image' => 'image',
                'keyword' => 'focus keyword',
                'keyword_in_title' => 'keyword in title',
                'keyword_in_description' => 'keyword in description',
                'keyword_in_intro' => 'keyword in first paragraph',
                'length' => 'content length',
            ],
        ]);
    }
}
