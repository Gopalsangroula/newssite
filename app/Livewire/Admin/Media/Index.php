<?php

namespace App\Livewire\Admin\Media;

use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Media Library')]
class Index extends Component
{
    use AuthorizesMedia, WithPagination;

    public const PER_PAGE = [24, 48, 96];

    public const SORTS = [
        'newest' => ['created_at', 'desc', 'Newest first'],
        'oldest' => ['created_at', 'asc', 'Oldest first'],
        'name-asc' => ['original_name', 'asc', 'Name A to Z'],
        'name-desc' => ['original_name', 'desc', 'Name Z to A'],
        'size-desc' => ['size', 'desc', 'Largest first'],
        'size-asc' => ['size', 'asc', 'Smallest first'],
        'type-asc' => ['mime_type', 'asc', 'Type A to Z'],
        'type-desc' => ['mime_type', 'desc', 'Type Z to A'],
    ];

    #[Url(as: 'q', except: '')]
    public $search = '';

    #[Url(except: '')]
    public $type = '';

    #[Url(except: '')]
    public $month = '';

    #[Url(except: 'newest')]
    public $sort = 'newest';

    #[Url(except: 24)]
    public $perPage = 24;

    #[Url(except: 'grid')]
    public $mode = 'grid';

    // The id of the item open in the details view, or '' when it is closed.
    // Not null: Livewire would then keep an empty ?item= in the url.
    #[Url(except: '')]
    public $item = '';

    public function mount()
    {
        if (! request()->has('mode')) {
            $this->mode = session('media.mode', 'grid');
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'type', 'month', 'sort', 'perPage'], true)) {
            $this->resetPage();
        }

        // A selection must not outlive the filter it was made under: items
        // that are no longer on screen would be deleted along with the rest.
        if (in_array($property, ['search', 'type', 'month'], true)) {
            $this->dispatch('media-filtered');
        }
    }

    public function updatedMode()
    {
        session(['media.mode' => $this->mode]);
    }

    public function updatedPage()
    {
        $this->dispatch('media-page-changed');
    }

    public function clearFilters()
    {
        $this->reset('search', 'type', 'month');
        $this->resetPage();
        $this->dispatch('media-filtered');
    }

    #[On('upload-complete')]
    public function showNewUploads()
    {
        $this->reset('search', 'type', 'month', 'sort');
        $this->resetPage();
        $this->dispatch('media-filtered');
    }

    public function sortBy($field)
    {
        [$column, $direction] = $this->currentSort();
        $direction = $column === $field && $direction === 'asc' ? 'desc' : 'asc';

        foreach (self::SORTS as $key => $sort) {
            if ($sort[0] === $field && $sort[1] === $direction) {
                $this->sort = $key;
                $this->resetPage();
                return;
            }
        }
    }

    public function openItem($id)
    {
        $this->resetErrorBag();
        $this->item = $id;
    }

    public function closeItem()
    {
        $this->resetErrorBag();
        $this->item = '';
    }

    public function saveItem($id, $description)
    {
        if (! $this->canEditMedia()) {
            Log::warning('Unauthorized media edit attempt', [
                'user_id' => auth()->id(),
                'media_id' => $id,
            ]);
            $this->notify('You are not authorized to edit media.', 'danger');
            return false;
        }

        Validator::make(
            ['description' => $description],
            ['description' => 'nullable|string|max:255'],
            [
                'description.string' => 'Image description must be a valid string.',
                'description.max' => 'Image description must not exceed 255 characters.',
            ]
        )->validate();

        try {
            Media::findOrFail($id)->update(['img_desc' => $description === '' ? null : $description]);

            Log::info('Media updated successfully', [
                'media_id' => $id,
                'user_id' => auth()->id(),
            ]);
            $this->notify('Media updated successfully.');
            return true;
        } catch (\Exception $e) {
            Log::error('Media update failed: ' . $e->getMessage(), [
                'media_id' => $id,
                'user_id' => auth()->id(),
            ]);
            $this->notify('Failed to update media.', 'danger');
            return false;
        }
    }

    public function deleteMedia($mediaId)
    {
        $this->destroy([$mediaId]);
    }

    public function bulkDelete($ids)
    {
        $this->destroy((array) $ids);
    }

    protected function destroy(array $ids): void
    {
        if (! $this->canDeleteMedia()) {
            Log::warning('Unauthorized media deletion attempt', [
                'user_id' => auth()->id(),
                'media_ids' => $ids,
            ]);
            $this->notify('You are not authorized to delete media.', 'danger');
            return;
        }

        $ids = array_map('intval', array_filter($ids, 'is_numeric'));
        $deleted = 0;

        foreach (Media::whereIn('id', $ids)->get() as $media) {
            try {
                $media->deleteWithFile();
                $deleted++;

                Log::info('Media deleted successfully', [
                    'media_id' => $media->id,
                    'user_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                Log::error('Media deletion failed: ' . $e->getMessage(), [
                    'media_id' => $media->id,
                    'user_id' => auth()->id(),
                ]);
            }
        }

        if (in_array((int) $this->item, $ids, true)) {
            $this->item = '';
        }

        $failed = count($ids) - $deleted;

        if ($failed > 0) {
            $this->notify("{$deleted} deleted, {$failed} could not be deleted.", 'danger');
        } else {
            $this->notify($deleted === 1 ? 'Media deleted successfully.' : "{$deleted} items deleted.");
        }
    }

    protected function notify(string $message, string $status = 'success'): void
    {
        $this->dispatch('media-notice', message: $message, status: $status);
    }

    protected function currentSort(): array
    {
        return self::SORTS[$this->sort] ?? self::SORTS['newest'];
    }

    protected function mediaQuery(): Builder
    {
        $query = Media::query();
        $search = trim((string) $this->search);

        if ($search !== '') {
            $like = '%' . addcslashes($search, '\\%_') . '%';

            $query->where(function ($query) use ($like) {
                $query->where('original_name', 'like', $like)
                      ->orWhere('filename', 'like', $like)
                      ->orWhere('img_desc', 'like', $like)
                      ->orWhere('mime_type', 'like', $like);
            });
        }

        if (array_key_exists($this->type, Media::KINDS)) {
            $query->ofKind($this->type);
        }

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $this->month)) {
            $start = Carbon::createFromFormat('!Y-m', $this->month);
            $query->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]);
        }

        return $query;
    }

    // The id just before or after $media in the current filter and order,
    // across pages. id breaks ties, the same way the listing does.
    protected function neighbour(Media $media, bool $forward): ?int
    {
        [$column, $direction] = $this->currentSort();
        $value = $media->getRawOriginal($column);

        if ($value === null) {
            return null;
        }

        $ascending = ($direction === 'asc') === $forward;
        $operator = $ascending ? '>' : '<';
        $order = $ascending ? 'asc' : 'desc';

        return $this->mediaQuery()
            ->where(function ($query) use ($column, $operator, $value, $media) {
                $query->where($column, $operator, $value)
                      ->orWhere(function ($query) use ($column, $operator, $value, $media) {
                          $query->where($column, $value)->where('id', $operator, $media->id);
                      });
            })
            ->orderBy($column, $order)
            ->orderBy('id', $order)
            ->value('id');
    }

    protected function monthOptions(): array
    {
        return Media::query()
            ->whereNotNull('created_at')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym')
            ->mapWithKeys(fn ($ym) => [$ym => Carbon::createFromFormat('!Y-m', $ym)->format('F Y')])
            ->all();
    }

    public function render()
    {
        [$column, $direction] = $this->currentSort();
        $perPage = in_array((int) $this->perPage, self::PER_PAGE, true) ? (int) $this->perPage : self::PER_PAGE[0];

        $query = $this->mediaQuery()
            ->when($this->mode === 'list', fn ($query) => $query->with('mediable'))
            ->orderBy($column, $direction)
            ->orderBy('id', $direction);

        $mediaItems = $query->paginate($perPage);

        // Deleting the last items of the last page leaves that page empty.
        if ($mediaItems->isEmpty() && $mediaItems->currentPage() > 1) {
            $this->setPage($mediaItems->lastPage());
            $mediaItems = $query->paginate($perPage);
        }

        $detail = is_numeric($this->item) ? Media::with('mediable')->find((int) $this->item) : null;

        if (! $detail) {
            $this->item = '';
        }

        return view('livewire.admin.media.index', [
            'mediaItems' => $mediaItems,
            'months' => $this->monthOptions(),
            'sorts' => self::SORTS,
            'perPageOptions' => self::PER_PAGE,
            'sortColumn' => $column,
            'sortDirection' => $direction,
            'filtered' => trim((string) $this->search) !== '' || $this->type !== '' || $this->month !== '',
            'detail' => $detail,
            'previousId' => $detail ? $this->neighbour($detail, false) : null,
            'nextId' => $detail ? $this->neighbour($detail, true) : null,
            'canDelete' => $this->canDeleteMedia(),
            'canEdit' => $this->canEditMedia(),
        ]);
    }
}
