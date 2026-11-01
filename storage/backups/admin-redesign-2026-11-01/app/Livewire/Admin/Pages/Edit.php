<?php

namespace App\Livewire\Admin\Pages;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Page;
use App\Models\Media;
use Carbon\Carbon;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Edit extends Component
{
    use WithFileUploads;

    /* ------------------- Form fields ------------------- */
    public $page;
    public $title;
    public $slug;
    public $content;
    public $cover_image;
    public $cover_image_url;
    public $status = 'published';
    public $published_at;
    public $editor_image;

    /* ------------------- Page ID ------------------- */
    public $page_id;

    /* ---------------------------------------------------- */
    public function mount($id = null)
    {
        // Add debug logging
        Log::info('Page Edit Mount Called', [
            'provided_id' => $id,
            'user_id' => auth()->id(),
            'url' => request()->url(),
            'all_params' => request()->all()
        ]);

        if (!auth()->check()) {
            session()->flash('error', 'You must be logged in to edit pages.');
            return redirect()->route('admin.pages.index');
        }

        // Debug: Check what pages exist
        $allPages = Page::all();
        Log::info('Available Pages', [
            'page_count' => $allPages->count(),
            'page_ids' => $allPages->pluck('id')->toArray(),
            'page_titles' => $allPages->pluck('title')->toArray()
        ]);

        // First find the page - try different approaches
        $this->page = Page::find($id);
        
        // If not found by ID, try finding by other means
        if (!$this->page && $id) {
            Log::warning('Page not found by ID, trying slug or other methods', ['id' => $id]);
            // Try to find by slug if ID is string
            if (is_string($id) && !is_numeric($id)) {
                $this->page = Page::where('slug', $id)->first();
            }
        }

        if (!$this->page) {
            Log::error('PAGE NOT FOUND - Detailed info:', [
                'searched_id' => $id,
                'id_type' => gettype($id),
                'available_pages' => Page::pluck('title', 'id')->toArray()
            ]);
            session()->flash('error', "Page not found. ID: {$id}");
            return redirect()->route('admin.pages.index');
        }

        $this->page_id = $this->page->id;

        Log::info('Page found successfully', [
            'page_id' => $this->page_id,
            'page_title' => $this->page->title,
            'page_slug' => $this->page->slug
        ]);

        /* ---------------------------------------------------------
            Permission Check (edit content)
        --------------------------------------------------------- */
        try {
            $hasPermission = auth()->user()->hasPermissionTo('edit content', 'web');
            $isCreator = $this->page->created_by === auth()->id();
            
            Log::info('Permission Check', [
                'user_id' => auth()->id(),
                'has_permission' => $hasPermission,
                'is_creator' => $isCreator,
                'page_creator' => $this->page->created_by
            ]);

            if (!$hasPermission && !$isCreator) {
                Log::warning('Unauthorized page edit attempt', [
                    'user_id' => auth()->id(),
                    'page_id' => $id,
                    'has_permission' => $hasPermission,
                    'is_creator' => $isCreator,
                    'page_creator' => $this->page->created_by,
                ]);

                session()->flash('error', 'You are not authorized to edit this page.');
                return redirect()->route('admin.pages.index');
            }
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission edit content does not exist for web guard', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            session()->flash('error', 'Permission error. Please contact admin.');
            return redirect()->route('admin.pages.index');
        }

        // Load page data after permission check
        $this->loadPage();

        Log::info('Page edit mounted successfully', ['page_id' => $this->page_id]);
    }

    /* ---------------------------------------------------- */
    protected function rules()
    {
        return [
            'title'          => 'required|string|max:255',
            'slug'           => 'required|string|unique:pages,slug,' . $this->page_id,
            'content'        => 'required|string',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover_image_url'=> 'nullable|string',
            'status'         => 'required|in:published,draft,scheduled',
            'published_at'   => 'nullable|required_if:status,scheduled|date',
        ];
    }

    protected function draftRules()
    {
        return [
            'title'          => 'required|string|max:255',
            'slug'           => 'required|string|unique:pages,slug,' . $this->page_id,
            'content'        => 'required|string',
            'cover_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cover_image_url'=> 'nullable|string',
            'status'         => 'in:draft',
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
        Log::info('Setting cover image URL', ['url' => $url, 'user_id' => auth()->id(), 'page_id' => $this->page_id]);

        if (!$url || !is_string($url)) {
            $this->cover_image_url = $this->cover_image = null;
            return;
        }

        // Handle the URL conversion properly
        $baseUrl = rtrim(config('app.url'), '/') . '/storage/';
        $relativePath = str_replace($baseUrl, '', $url);
        
        $this->cover_image_url = Storage::url($relativePath);
        $this->cover_image = null;
        
        // Clear any previous errors
        $this->resetErrorBag('cover_image_url');
    }

    /* ---------------------------------------------------- */
    public function update()
    {
        $this->checkPermission('edit content');

        $this->status = $this->status ?? 'published';
        $validated = $this->validate($this->status === 'draft' ? $this->draftRules() : $this->rules());

        $this->generateSlug($validated);
        $coverPath = $this->handleCoverImage($validated);
        $publishedAt = $this->resolvePublishedAt($validated['status']);

        $pageData = [
            'title'        => $validated['title'],
            'slug'         => $validated['slug'],
            'content'      => $validated['content'],
            'cover_image'  => $coverPath,
            'status'       => $validated['status'],
            'published_at' => $publishedAt,
        ];

        // Update the page
        $this->page->update($pageData);

        // Link media to the page
        $this->linkMedia($this->page, $coverPath, $validated['content']);

        session()->flash('message', 'Page updated successfully.');
        $this->dispatch('resetEditor');

        return redirect()->route('admin.pages.index');
    }

    /* ---------------------------------------------------- */
    public function saveDraft()
    {
        $this->checkPermission('edit content');

        $this->status = 'draft';
        $validated = $this->validate($this->draftRules());

        $this->generateSlug($validated);
        $coverPath = $this->handleCoverImage($validated);

        $pageData = [
            'title'        => $validated['title'],
            'slug'         => $validated['slug'],
            'content'      => $validated['content'],
            'cover_image'  => $coverPath,
            'status'       => 'draft',
            'published_at' => null,
        ];

        $this->page->update($pageData);
        $this->linkMedia($this->page, $coverPath, $validated['content']);

        session()->flash('message', 'Page saved as draft successfully.');
        $this->dispatch('resetEditor');

        return redirect()->route('admin.pages.index');
    }

    /* ---------------------------------------------------- */
    public function deletePage()
    {
        $this->checkPermission('delete content');

        $pageId = $this->page->id;
        $pageTitle = $this->page->title;

        // Delete associated media
        $this->page->media()->delete();
        $this->page->delete();

        Log::info('Page deleted', [
            'page_id' => $pageId,
            'page_title' => $pageTitle,
            'user_id' => auth()->id()
        ]);

        session()->flash('message', 'Page deleted successfully.');

        return redirect()->route('admin.pages.index');
    }

    /* ---------------------------------------------------- */
    public function uploadEditorImage()
    {
        $this->validate(['editor_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048']);

        $folder   = 'media/editor/' . now()->format('Y/m');
        $original = $this->editor_image->getClientOriginalName();
        $ext      = $this->editor_image->getClientOriginalExtension();
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
            'mediable_id'   => $this->page_id,
            'mediable_type' => Page::class,
            'img_desc'      => '',
        ]);

        Log::info('Editor image uploaded', [
            'path' => $path,
            'user_id' => auth()->id(),
            'page_id' => $this->page_id
        ]);

        return response()->json(['location' => $url]);
    }

    /* ---------------------------------------------------- */
    protected function loadPage()
    {
        if ($this->page) {
            $this->title           = $this->page->title;
            $this->slug            = $this->page->slug;
            $this->content         = $this->page->content;
            $this->cover_image_url = $this->page->cover_image ? Storage::url($this->page->cover_image) : null;
            $this->status          = $this->page->status;
            $this->published_at    = $this->page->published_at?->format('Y-m-d\TH:i');
        }
    }

    protected function checkPermission(string $perm)
    {
        try {
            $hasPermission = auth()->user()->hasPermissionTo($perm, 'web');
            $isCreator = $this->page->created_by === auth()->id();
            
            if (!$hasPermission && !$isCreator) {
                Log::warning("Unauthorized {$perm} attempt", [
                    'user_id' => auth()->id(),
                    'page_id' => $this->page_id,
                    'has_permission' => $hasPermission,
                    'is_creator' => $isCreator,
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
            while (Page::where('slug', $slug)->where('id', '!=', $this->page_id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $validated['slug'] = $slug;
        } else {
            // Ensure slug is properly formatted
            $validated['slug'] = Str::slug($validated['slug']);
        }
    }

    protected function handleCoverImage($validated)
    {
        $coverPath = $this->page->cover_image;

        if ($this->cover_image) {
            // New file upload
            $coverPath = $this->storeUploadedCover($this->cover_image);
        } elseif ($this->cover_image_url && $this->cover_image_url !== Storage::url($coverPath)) {
            // URL from media library
            $baseUrl = rtrim(config('app.url'), '/') . '/storage/';
            $relativePath = str_replace($baseUrl, '', $this->cover_image_url);
            
            $media = Media::where('url', $this->cover_image_url)->first();
            
            if ($media) {
                $coverPath = $relativePath;
            } else {
                $this->addError('cover_image_url', 'Selected media not found in library.');
                // Keep existing cover image on error
                return $this->page->cover_image;
            }
        }

        return $coverPath;
    }

    protected function storeUploadedCover($file)
    {
        $folder   = 'media/' . now()->format('Y/m');
        $original = $file->getClientOriginalName();
        $ext      = $file->getClientOriginalExtension();
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
            'mediable_id'   => $this->page_id,
            'mediable_type' => Page::class,
            'img_desc'      => 'Page cover image',
        ]);

        return $path;
    }

    protected function resolvePublishedAt($status)
    {
        return match ($status) {
            'published' => now(),
            'scheduled' => $this->published_at ? Carbon::parse($this->published_at) : null,
            default     => null,
        };
    }

    protected function linkMedia(Page $page, $coverPath, $content)
    {
        // Link cover image media
        if ($coverPath) {
            $coverUrl = Storage::url($coverPath);
            Media::where('url', $coverUrl)
                ->update([
                    'mediable_id' => $page->id, 
                    'mediable_type' => Page::class
                ]);
        }

        // Link content images
        $imageUrls = $this->extractImageUrls($content);
        if (!empty($imageUrls)) {
            Media::whereIn('url', $imageUrls)
                ->update([
                    'mediable_id' => $page->id, 
                    'mediable_type' => Page::class
                ]);
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
        return view('livewire.admin.pages.edit');
    }
}