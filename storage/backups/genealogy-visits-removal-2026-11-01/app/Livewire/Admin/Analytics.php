<?php

namespace App\Livewire\Admin;

use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Editors see per-post counts; the visitor log, which holds IP addresses, is
 * for admins. Roles are read from the session on every request, and the log
 * query runs only for admins, because Livewire properties can be changed by
 * the browser and route middleware does not run on Livewire updates.
 */
#[Title('Analytics')]
class Analytics extends Component
{
    use WithPagination;

    private const RANGES = [7, 30, 90];
    private const PER_PAGE = 15;

    #[Url]
    public $days = 30;

    #[Url]
    public $sort = 'desc';

    public $search = '';

    public function boot(): void
    {
        abort_unless(Visit::canViewStats(Auth::user()), 403);
    }

    public function updatedDays(): void
    {
        $this->resetPage('postsPage');
        $this->resetPage('visitsPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('visitsPage');
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'asc' ? 'desc' : 'asc';
        $this->resetPage('postsPage');
    }

    public function render()
    {
        $this->days = in_array((int) $this->days, self::RANGES, true) ? (int) $this->days : 30;
        $this->sort = $this->sort === 'asc' ? 'asc' : 'desc';

        $since = today()->subDays($this->days - 1);
        $canSeeDetails = Visit::canViewDetails(Auth::user());

        return view('livewire.admin.analytics', [
            'ranges' => self::RANGES,
            'canSeeDetails' => $canSeeDetails,
            'summary' => $this->summary($since),
            'posts' => $this->posts($since),
            'visits' => $canSeeDetails ? $this->visitLog($since) : null,
        ]);
    }

    protected function summary(CarbonInterface $since): object
    {
        return Visit::where('created_at', '>=', $since)
            ->selectRaw('COUNT(*) AS views, COUNT(DISTINCT visitor_key) AS visitors, COUNT(DISTINCT post_id) AS posts')
            ->toBase()
            ->first();
    }

    protected function posts(CarbonInterface $since): LengthAwarePaginator
    {
        return Visit::postStats($since)
            ->orderBy('totals.views', $this->sort)
            ->orderBy('posts.id')
            ->paginate(self::PER_PAGE, pageName: 'postsPage');
    }

    protected function visitLog(CarbonInterface $since): LengthAwarePaginator
    {
        $search = addcslashes(trim((string) $this->search), '%_\\');

        return Visit::with('post:id,title')
            ->where('created_at', '>=', $since)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->paginate(self::PER_PAGE, pageName: 'visitsPage');
    }
}
