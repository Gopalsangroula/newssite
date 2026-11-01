<?php

namespace App\Livewire\Admin\Pages;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Page;
use App\Models\Media;
use Carbon\Carbon;
use App\Livewire\Admin\Concerns\HasSeoFields;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

#[Title('Add Page')]
class Create extends Component
{
    use WithFileUploads, HasSeoFields;

    /* ------------------- Form fields ------------------- */
    public $title;
    public $slug;
    public $content;
    public $cover_image;
    public $cover_image_url;
    public $status = 'published';
    public $published_at;
    public $editor_image;

    /* ------------------- Draft handling ------------------- */
    public $temporaryPage;
    public $draft_id;

    /* ---------------------------------------------------- */
    public function mount($draft_id = null)
    {
        if (!auth()->check()) {
            session()->flash('error', 'You must be logged in.');
            return redirect()->route('admin.pages.index');
        }

        $this->checkPermission('create content');

        $this->published_at = now()->format('Y-m-d\TH:i');
        $this->draft_id     = $draft_id;

        if ($this->draft_id) {
            $this->loadDraft();
        } else {
            session()->forget('temporary_page_id');
        }
    }

    /* ---------------------------------------------------- */
    protected function rules()
    {
        return [
            'title'          => 'required|string|max:255',
            'slug'           => 'nullable|string|unique:pages,slug,' . ($this->temporaryPage?->id ?? 'NULL'),
            'content'        => 'required|string',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover_image_url'=> 'nullable|string',
            'status'         => 'required|in:published,draft,scheduled',
            'published_at'   => 'nullable|required_if:status,scheduled|date',
            ...$this->seoRules(),
        ];
    }

    protected function draftRules()
    {
        return [
            'title'          => 'required|string|max:255',
            'slug'           => 'nullable|string|unique:pages,slug,' . ($this->temporaryPage?->id ?? 'NULL'),
            'content'        => 'required|string',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover_image_url'=> 'nullable|string',
            'status'         => 'in:draft',
            ...$this->seoRules(),
        ];
    }

    public function updated($property)
    {
        $this->validateOnly($property, $this->status === 'draft' ? $this->draftRules() : $this->rules());
    }

    public function updatedCoverImage()
    {
        $this->cover_image_url = null;
        $this->validateOnly('cover_image');
    }

    public function setCoverImage($url)
    {
        Log::info('Setting cover image URL', ['url' => $url, 'user_id' => auth()->id()]);

        if (!$url || !is_string($url)) {
            $this->cover_image_url = $this->cover_image = null;
            return;
        }

        $this->cover_image_url = $url;
        $this->cover_image = null;
        $this->validateOnly('cover_image_url');
    }

    /* ---------------------------------------------------- */
    public function newPage()
    {
        $this->checkPermission('create content');

        $this->reset([
            'title','slug','content','cover_image','cover_image_url',
            'status','published_at','temporaryPage','draft_id',
        ]);

        $this->published_at = now()->format('Y-m-d\TH:i');
        session()->forget('temporary_page_id');
        $this->dispatch('resetEditor');
    }

    /* ---------------------------------------------------- */
    public function save()
    {
        $this->status = $this->status ?? 'published';
        $validated = $this->validate($this->status === 'draft' ? $this->draftRules() : $this->rules());

        $this->generateSlug($validated);
        $coverPath = $this->handleCoverImage($validated);
        $publishedAt = $this->resolvePublishedAt($validated['status']);

        $pageData = [
            'created_by'   => auth()->id(),
            'title'        => $validated['title'],
            'slug'         => $validated['slug'],
            'content'      => $validated['content'],
            'cover_image'  => $coverPath,
            'status'       => $validated['status'],
            'published_at' => $publishedAt,
            ...$this->seoData(),
        ];

        if ($this->temporaryPage) {
            $this->checkPermission('edit content', $this->temporaryPage);
            $this->temporaryPage->update($pageData);
            $page = $this->temporaryPage;
        } else {
            $page = Page::create($pageData);
        }

        $this->linkMedia($page, $coverPath, $validated['content']);

        session()->flash('message', 'Page saved successfully.');
        session()->forget('temporary_page_id');
        $this->dispatch('resetEditor');

        return redirect()->route('admin.pages.index');
    }

    /* ---------------------------------------------------- */
    public function saveDraft()
    {
        $this->status = 'draft';
        $validated = $this->validate($this->draftRules());

        $this->generateSlug($validated);
        $coverPath = $this->handleCoverImage($validated);

        $pageData = [
            'created_by'   => auth()->id(),
            'title'        => $validated['title'],
            'slug'         => $validated['slug'],
            'content'      => $validated['content'],
            'cover_image'  => $coverPath,
            'status'       => 'draft',
            'published_at' => null,
            ...$this->seoData(),
        ];

        if ($this->temporaryPage) {
            $this->checkPermission('edit content', $this->temporaryPage);
            $this->temporaryPage->update($pageData);
            $page = $this->temporaryPage;
        } else {
            $page = Page::create($pageData);
        }

        $this->linkMedia($page, $coverPath, $validated['content']);

        session()->put('temporary_page_id', $page->id);
        session()->flash('message', 'Draft saved successfully.');
        $this->dispatch('resetEditor');

        return redirect()->route('admin.pages.index');
    }

    /* ---------------------------------------------------- */
    public function deleteDraft()
    {
        if (!$this->temporaryPage) {
            session()->flash('error', 'No draft to delete.');
            return;
        }

        $this->checkPermission('delete content', $this->temporaryPage);

        $pageId = $this->temporaryPage->id;
        $this->temporaryPage->delete();

        Log::info('Page draft deleted', ['page_id' => $pageId, 'user_id' => auth()->id()]);
        session()->flash('message', 'Draft deleted.');
        session()->forget('temporary_page_id');

        return redirect()->route('admin.pages.index');
    }

    /* ---------------------------------------------------- */
    public function uploadEditorImage()
    {
        $this->validate(['editor_image' => 'required|image|mimes:jpeg,png,jpg|max:2048']);

        $folder   = 'media/editor/' . now()->format('Y/m');
        $original = $this->editor_image->getClientOriginalName();
        $ext      = $this->editor_image->extension();
        $name     = Str::slug(pathinfo($original, PATHINFO_FILENAME));
        $final    = $name . '.' . $ext;
        $i        = 1;

        while (Storage::disk('public')->exists("$folder/$final")) {
            $final = $name . '-' . $i++ . '.' . $ext;
        }

        $path = $this->editor_image->storeAs($folder, $final, 'public');
        $url  = Storage::url($path);

        Media::create([
            'filename'      => $final,
            'original_name' => $original,
            'mime_type'     => $this->editor_image->getMimeType(),
            'size'          => $this->editor_image->getSize(),
            'url'           => $url,
            'mediable_id'   => $this->temporaryPage?->id ?? 0,
            'mediable_type' => Page::class,
            'img_desc'      => '',
        ]);

        Log::info('Editor image uploaded', ['path' => $path, 'user_id' => auth()->id()]);

        return response()->json(['location' => $url]);
    }

    /* ---------------------------------------------------- */
    protected function loadDraft()
    {
        $this->temporaryPage = Page::withTrashed()
            ->where('id', $this->draft_id)
            ->where('status', 'draft')
            ->where('created_by', auth()->id())
            ->first();

        if (!$this->temporaryPage) {
            Log::warning('Draft not found', ['draft_id' => $this->draft_id, 'user_id' => auth()->id()]);
            session()->flash('error', 'Draft not found or not yours.');
            session()->forget('temporary_page_id');
            return redirect()->route('admin.pages.index');
        }

        $this->checkPermission('edit content', $this->temporaryPage);

        $this->title          = $this->temporaryPage->title !== 'Draft' ? $this->temporaryPage->title : null;
        $this->slug           = $this->temporaryPage->slug;
        $this->content        = $this->temporaryPage->content !== 'Temporary draft content' ? $this->temporaryPage->content : null;
        $this->cover_image_url= $this->temporaryPage->cover_image ? Storage::url($this->temporaryPage->cover_image) : null;
        $this->status         = $this->temporaryPage->status;
        $this->published_at   = $this->temporaryPage->published_at?->format('Y-m-d\TH:i');
        $this->fillSeo($this->temporaryPage);

        session()->put('temporary_page_id', $this->temporaryPage->id);
    }

    protected function checkPermission(string $perm, $model = null)
    {
        try {
            if (!auth()->user()->hasPermissionTo($perm, 'web') &&
                (!$model || $model->created_by !== auth()->id())
            ) {
                Log::warning("Unauthorized {$perm} attempt", [
                    'user_id' => auth()->id(),
                    'model'   => $model?->getTable() ?? null,
                    'model_id'=> $model?->id ?? null,
                ]);
                session()->flash('error', 'You are not authorized.');
                return redirect()->route('admin.pages.index');
            }
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission missing', ['exception' => $e->getMessage()]);
            session()->flash('error', 'Permission error.');
            return redirect()->route('admin.pages.index');
        }
    }

    protected function generateSlug(&$validated)
    {
        if (empty($validated['slug'])) {
            $base = Str::slug($validated['title']);
            $slug = $base;
            $i    = 1;
            while (Page::where('slug', $slug)->where('id', '!=', $this->temporaryPage?->id ?? 0)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $validated['slug'] = $slug;
        }
    }

    protected function handleCoverImage($validated)
    {
        $coverPath = $this->temporaryPage?->cover_image;

        if ($this->cover_image) {
            $coverPath = $this->storeUploadedCover($this->cover_image);
        } elseif ($this->cover_image_url) {
            $relative = ltrim(str_replace(rtrim(Storage::url(''), '/'), '', $this->cover_image_url), '/');
            $media    = Media::where('url', $this->cover_image_url)->first();

            if ($media) {
                $coverPath = $relative;
            } else {
                $this->addError('cover_image_url', 'Selected media not found.');
                $coverPath = null;
            }
        }

        return $coverPath;
    }

    protected function storeUploadedCover($file)
    {
        $folder   = 'media/' . now()->format('Y/m');
        $original = $file->getClientOriginalName();
        $ext      = $file->extension();
        $name     = Str::slug(pathinfo($original, PATHINFO_FILENAME));
        $final    = $name . '.' . $ext;
        $i        = 1;

        while (Storage::disk('public')->exists("$folder/$final")) {
            $final = $name . '-' . $i++ . '.' . $ext;
        }

        $path = $file->storeAs($folder, $final, 'public');

        Media::create([
            'filename'      => $final,
            'original_name' => $original,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'url'           => Storage::url($path),
            'mediable_id'   => $this->temporaryPage?->id ?? 0,
            'mediable_type' => Page::class,
            'img_desc'      => '',
        ]);

        return $path;
    }

    protected function resolvePublishedAt($status)
    {
        return match ($status) {
            'published' => now(),
            'scheduled' => Carbon::parse($this->published_at),
            default     => null,
        };
    }

    protected function linkMedia(Page $page, $coverPath, $content)
    {
        if ($coverPath) {
            Media::where('url', Storage::url($coverPath))
                ->update(['mediable_id' => $page->id, 'mediable_type' => Page::class]);
        }

        $imageUrls = $this->extractImageUrls($content);
        if ($imageUrls) {
            Media::whereIn('url', $imageUrls)
                ->update(['mediable_id' => $page->id, 'mediable_type' => Page::class]);
        }
    }

    protected function extractImageUrls($content)
    {
        if (!$content) return [];

        preg_match_all('/<img[^>]+src=["\'](.*?)["\']/i', $content, $matches);
        return array_filter($matches[1] ?? []);
    }

    /* ---------------------------------------------------- */
    public function render()
    {
        return view('livewire.admin.pages.create');
    }
}