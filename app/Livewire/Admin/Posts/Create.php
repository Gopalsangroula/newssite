<?php

namespace App\Livewire\Admin\Posts;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use App\Models\User;
use Carbon\Carbon;
use App\Livewire\Admin\Concerns\HasSeoFields;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

#[Title('Add Post')]
class Create extends Component
{
    use WithFileUploads, HasSeoFields;

    public $title;
    public $sub_title;
    public $slug;
    public $category_id;
    public array $extra_categories = [];
    public $content;
    public $cover_image;
    public $cover_image_url;
    public $is_featured = false;
    public $is_breaking = false;
    public $status = 'published';
    public $published_at;
    public $tags = [];
    public $user_ids = [];
    public $editor_image;
    public $temporaryPost;
    public $draft_id;

    public $categories;
    public $allTags;
    public $users;

    public function mount($draft_id = null)
    {
        if (!auth()->check()) {
            session()->flash('error', 'You must be logged in to create posts.');
            return redirect()->route('admin.posts.index');
        }

        try {
            if (!auth()->user()->hasPermissionTo('create content', 'web')) {
                Log::warning('Unauthorized post creation attempt: Missing create content permission', [
                    'user_id' => auth()->id(),
                ]);
                session()->flash('error', 'You are not authorized to create posts.');
                return redirect()->route('admin.posts.index');
            }
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission create content does not exist for web guard', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);
            session()->flash('error', 'Permission configuration error. Please contact the administrator.');
            return redirect()->route('admin.posts.index');
        }

        $this->categories = Category::with('children')->whereNull('parent_id')->get();
        $this->allTags = Tag::all();
        $this->users = User::select('id', 'name')->get();
        $this->published_at = now()->format('Y-m-d\TH:i');
        $this->user_ids = [auth()->id()];
        $this->draft_id = $draft_id;

        if ($this->categories->isEmpty()) {
            $category = Category::create([
                'name' => 'Default Category',
                'slug' => 'default-category',
            ]);
            $this->categories = Category::with('children')->whereNull('parent_id')->get();
            $this->category_id = $category->id;
        }

        if ($this->draft_id) {
            $this->temporaryPost = Post::withTrashed()
                ->where('id', $this->draft_id)
                ->where('status', 'draft')
                ->where('created_by', auth()->id())
                ->first();

            if ($this->temporaryPost) {
                try {
                    if (!auth()->user()->hasPermissionTo('edit content', 'web') && 
                        $this->temporaryPost->created_by !== auth()->id()) {
                        Log::warning('Unauthorized draft edit attempt', [
                            'user_id' => auth()->id(),
                            'draft_id' => $this->draft_id,
                        ]);
                        session()->flash('error', 'You are not authorized to edit this draft.');
                        return redirect()->route('admin.posts.index');
                    }
                } catch (PermissionDoesNotExist $e) {
                    Log::error('Permission edit content does not exist for web guard', [
                        'user_id' => auth()->id(),
                        'exception' => $e->getMessage(),
                    ]);
                    session()->flash('error', 'Permission configuration error. Please contact the administrator.');
                    return redirect()->route('admin.posts.index');
                }

                $this->title = $this->temporaryPost->title !== 'Draft' ? $this->temporaryPost->title : null;
                $this->sub_title = $this->temporaryPost->sub_title;
                $this->slug = $this->temporaryPost->slug;
                $this->category_id = $this->temporaryPost->category_id;
                $this->extra_categories = $this->temporaryPost->extraCategories->pluck('id')->map(fn ($id) => (string) $id)->all();
                $this->content = $this->temporaryPost->content !== 'Temporary draft content' ? $this->temporaryPost->content : null;
                
                if ($this->temporaryPost->cover_image) {
                    $coverUrl = Storage::url($this->temporaryPost->cover_image);
                    $coverUrl = preg_replace('#/storage/+/storage/#', '/storage/', $coverUrl);
                    $coverUrl = str_replace('/storage/storage/', '/storage/', $coverUrl);
                    $this->cover_image_url = $coverUrl;
                } else {
                    $this->cover_image_url = null;
                }
                
                $this->is_featured = $this->temporaryPost->is_featured;
                $this->is_breaking = $this->temporaryPost->is_breaking;
                $this->tags = $this->temporaryPost->tags->pluck('id')->toArray();
                $this->user_ids = $this->temporaryPost->users->pluck('id')->toArray();
                $this->fillSeo($this->temporaryPost);
                session()->put('temporary_post_id', $this->temporaryPost->id);
            } else {
                Log::warning('Draft not found or unauthorized', [
                    'user_id' => auth()->id(),
                    'draft_id' => $this->draft_id,
                ]);
                session()->flash('error', 'Draft not found or you are not authorized to edit it.');
                session()->forget('temporary_post_id');
                return redirect()->route('admin.posts.index');
            }
        } else {
            session()->forget('temporary_post_id');
        }
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . ($this->temporaryPost ? $this->temporaryPost->id : null),
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_image_url' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|required_if:status,scheduled|date',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            ...$this->seoRules(),
        ];
    }

    protected function draftRules()
    {
        return [
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . ($this->temporaryPost ? $this->temporaryPost->id : null),
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_image_url' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'status' => 'in:draft',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            ...$this->seoRules(),
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->status === 'draft' ? $this->draftRules() : $this->rules());
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
            Log::warning('Invalid or empty cover image URL provided', ['url' => $url, 'user_id' => auth()->id()]);
            $this->cover_image_url = null;
            $this->cover_image = null;
            return;
        }

        // Clean the URL - remove any double storage patterns
        $cleanUrl = preg_replace('#/storage/+/storage/#', '/storage/', $url);
        $cleanUrl = str_replace('/storage/storage/', '/storage/', $cleanUrl);
        $cleanUrl = str_replace('storage/storage/', 'storage/', $cleanUrl);
        
        // If it's already a valid full URL, use it directly
        if (filter_var($cleanUrl, FILTER_VALIDATE_URL)) {
            $this->cover_image_url = $cleanUrl;
        } else {
            // Extract the path and convert to full URL
            if (preg_match('#/storage/(.+)$#', $cleanUrl, $matches)) {
                $relativePath = $matches[1];
                $this->cover_image_url = Storage::url($relativePath);
            } else {
                $cleanPath = ltrim($cleanUrl, '/');
                $cleanPath = str_replace('storage/', '', $cleanPath);
                $this->cover_image_url = Storage::url($cleanPath);
            }
        }
        
        $this->cover_image = null;
        $this->validateOnly('cover_image_url');
        
        Log::info('Cover image URL set to', ['cover_image_url' => $this->cover_image_url]);
    }

    public function newPost()
    {
        try {
            if (!auth()->user()->hasPermissionTo('create content', 'web')) {
                Log::warning('Unauthorized post creation attempt: Missing create content permission', [
                    'user_id' => auth()->id(),
                ]);
                session()->flash('error', 'You are not authorized to create posts.');
                return redirect()->route('admin.posts.index');
            }
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission create content does not exist for web guard', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);
            session()->flash('error', 'Permission configuration error. Please contact the administrator.');
            return redirect()->route('admin.posts.index');
        }

        $this->reset([
            'title', 'sub_title', 'slug', 'category_id', 'extra_categories', 'content',
            'cover_image', 'cover_image_url', 'tags',
            'is_featured', 'is_breaking', 'status',
            'published_at', 'temporaryPost', 'draft_id',
            'user_ids', 'editor_image',
        ]);
        $this->published_at = now()->format('Y-m-d\TH:i');
        $this->user_ids = [auth()->id()];
        session()->forget('temporary_post_id');
        $this->dispatch('resetEditor');
    }

    /**
     * Generate a unique filename from the original name, with the extension of what the file really is
     */
    private function getUniqueFilename($originalName, $folder, $extension)
    {
        // Get file info
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Clean filename - remove special characters and spaces
        $cleanFilename = preg_replace('/[^a-zA-Z0-9-]/', '-', $filename);
        $cleanFilename = preg_replace('/-+/', '-', $cleanFilename);
        $cleanFilename = trim($cleanFilename, '-');
        
        // If filename is empty after cleaning, use a default
        if (empty($cleanFilename)) {
            $cleanFilename = 'image';
        }
        
        // Check if file exists
        $finalName = $cleanFilename . '.' . $extension;
        $counter = 1;
        
        while (Storage::disk('public')->exists("$folder/$finalName")) {
            $finalName = $cleanFilename . '-' . $counter++ . '.' . $extension;
        }
        
        return $finalName;
    }

    public function save()
    {
        if ($this->temporaryPost) {
            try {
                if (!auth()->user()->hasPermissionTo('edit content', 'web') && 
                    $this->temporaryPost->created_by !== auth()->id()) {
                    Log::warning('Unauthorized post update attempt', [
                        'user_id' => auth()->id(),
                        'post_id' => $this->temporaryPost->id,
                    ]);
                    session()->flash('error', 'You are not authorized to edit this post.');
                    return redirect()->route('admin.posts.index');
                }
            } catch (PermissionDoesNotExist $e) {
                Log::error('Permission edit content does not exist for web guard', [
                    'user_id' => auth()->id(),
                    'exception' => $e->getMessage(),
                ]);
                session()->flash('error', 'Permission configuration error. Please contact the administrator.');
                return redirect()->route('admin.posts.index');
            }
        }

        try {
            $validated = $this->validate();
            if ($this->getErrorBag()->any()) {
                Log::info('Validation errors', ['errors' => $this->getErrorBag()->toArray(), 'user_id' => auth()->id()]);
                session()->flash('error', 'Validation failed: ' . implode(', ', $this->getErrorBag()->all()));
                return;
            }

            if (empty($validated['slug'])) {
                $baseSlug = Str::slug($validated['title']);
                $slug = $baseSlug;
                $counter = 1;
                while (Post::where('slug', $slug)->where('id', '!=', $this->temporaryPost ? $this->temporaryPost->id : 0)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $validated['slug'] = $slug;
            }

            $coverImage = $this->temporaryPost ? $this->temporaryPost->cover_image : null;
            
            if ($this->cover_image) {
                $folder = 'media/' . now()->format('Y/m');
                $originalName = $this->cover_image->getClientOriginalName();
                
                // Get unique filename preserving original name
                $finalName = $this->getUniqueFilename($originalName, $folder, $this->cover_image->extension());
                $coverPath = $this->cover_image->storeAs($folder, $finalName, 'public');
                $coverImage = $coverPath;

                $media = Media::create([
                    'filename' => $finalName,
                    'original_name' => $originalName,
                    'mime_type' => $this->cover_image->getMimeType(),
                    'size' => $this->cover_image->getSize(),
                    'url' => Storage::url($coverPath),
                    'mediable_id' => $this->temporaryPost ? $this->temporaryPost->id : 0,
                    'mediable_type' => Post::class,
                    'img_desc' => '',
                ]);

                Log::info('New cover image uploaded and saved to Media', [
                    'path' => $coverPath,
                    'original_name' => $originalName,
                    'final_name' => $finalName,
                    'media_id' => $media->id,
                    'user_id' => auth()->id(),
                ]);
            } elseif ($this->cover_image_url) {
                // Extract the relative path from the full URL
                if (preg_match('#/storage/(.+)$#', $this->cover_image_url, $matches)) {
                    $coverImage = $matches[1];
                } else {
                    $coverImage = str_replace(Storage::url(''), '', $this->cover_image_url);
                    $coverImage = ltrim($coverImage, '/');
                }
                
                Log::info('Cover image set from media library', [
                    'cover_image_url' => $this->cover_image_url,
                    'cover_image_path' => $coverImage,
                    'user_id' => auth()->id(),
                ]);
            }

            $publishedAt = null;
            if ($validated['status'] === 'published') {
                $publishedAt = now();
            } elseif ($validated['status'] === 'scheduled') {
                $publishedAt = Carbon::parse($this->published_at);
            }

            $postData = [
                'created_by' => auth()->id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'sub_title' => $validated['sub_title'],
                'slug' => $validated['slug'],
                'content' => $validated['content'],
                'cover_image' => $coverImage,
                'is_featured' => $this->is_featured,
                'is_breaking' => $this->is_breaking,
                'status' => $validated['status'],
                'published_at' => $publishedAt,
                ...$this->seoData(),
            ];

            if ($this->temporaryPost) {
                $this->temporaryPost->update($postData);
                $post = $this->temporaryPost;
            } else {
                $post = Post::create($postData);
            }

            // Sync authors
            $post->users()->sync($this->user_ids);
            $post->extraCategories()->sync(array_diff($this->extra_categories, [$this->category_id]));

            Log::info('Post saved', [
                'post_id' => $post->id,
                'title' => $post->title,
                'cover_image' => $post->cover_image,
                'created_by' => auth()->user()->name . ' (ID: ' . auth()->id() . ')',
                'authors' => User::whereIn('id', $this->user_ids)->pluck('name', 'id')->toArray(),
            ]);

            if ($coverImage) {
                Media::where('url', Storage::url($coverImage))
                    ->update([
                        'mediable_id' => $post->id,
                        'mediable_type' => Post::class,
                    ]);
            }

            $imageUrls = $this->extractImageUrls($validated['content']);
            if (!empty($imageUrls)) {
                foreach ($imageUrls as $imageUrl) {
                    Media::where('url', $imageUrl)
                        ->update([
                            'mediable_id' => $post->id,
                            'mediable_type' => Post::class,
                        ]);
                }
            }

            if (!empty($this->tags)) {
                $post->tags()->sync($this->tags);
            } else {
                $post->tags()->detach();
            }

            session()->flash('message', 'Post saved successfully.');
            session()->forget('temporary_post_id');
            $this->dispatch('resetEditor');
            
            return redirect()->route('admin.posts.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Post save failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Failed to save post: ' . $e->getMessage());
        }
    }

    public function saveDraft()
    {
        if ($this->temporaryPost) {
            try {
                if (!auth()->user()->hasPermissionTo('edit content', 'web') && 
                    $this->temporaryPost->created_by !== auth()->id()) {
                    Log::warning('Unauthorized draft update attempt', [
                        'user_id' => auth()->id(),
                        'draft_id' => $this->temporaryPost->id,
                    ]);
                    session()->flash('error', 'You are not authorized to edit this draft.');
                    return redirect()->route('admin.posts.index');
                }
            } catch (PermissionDoesNotExist $e) {
                Log::error('Permission edit content does not exist for web guard', [
                    'user_id' => auth()->id(),
                    'exception' => $e->getMessage(),
                ]);
                session()->flash('error', 'Permission configuration error. Please contact the administrator.');
                return redirect()->route('admin.posts.index');
            }
        }

        try {
            $this->status = 'draft';
            $validated = $this->validate($this->draftRules());
            if ($this->getErrorBag()->any()) {
                Log::info('Draft validation errors', ['errors' => $this->getErrorBag()->toArray(), 'user_id' => auth()->id()]);
                session()->flash('error', 'Validation failed: ' . implode(', ', $this->getErrorBag()->all()));
                return;
            }

            if (empty($validated['slug'])) {
                $baseSlug = Str::slug($validated['title']);
                $slug = $baseSlug;
                $counter = 1;
                while (Post::where('slug', $slug)->where('id', '!=', $this->temporaryPost ? $this->temporaryPost->id : 0)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $validated['slug'] = $slug;
            }

            $coverImage = $this->temporaryPost ? $this->temporaryPost->cover_image : null;

            if ($this->cover_image) {
                $folder = 'media/' . now()->format('Y/m');
                $originalName = $this->cover_image->getClientOriginalName();
                
                // Get unique filename preserving original name
                $finalName = $this->getUniqueFilename($originalName, $folder, $this->cover_image->extension());
                $coverPath = $this->cover_image->storeAs($folder, $finalName, 'public');
                $coverImage = $coverPath;

                $media = Media::create([
                    'filename' => $finalName,
                    'original_name' => $originalName,
                    'mime_type' => $this->cover_image->getMimeType(),
                    'size' => $this->cover_image->getSize(),
                    'url' => Storage::url($coverPath),
                    'mediable_id' => $this->temporaryPost ? $this->temporaryPost->id : 0,
                    'mediable_type' => Post::class,
                    'img_desc' => '',
                ]);

                Log::info('New cover image uploaded and saved to Media for draft', [
                    'path' => $coverPath,
                    'original_name' => $originalName,
                    'final_name' => $finalName,
                    'media_id' => $media->id,
                    'user_id' => auth()->id(),
                ]);
            } elseif ($this->cover_image_url) {
                // Extract the relative path from the full URL
                if (preg_match('#/storage/(.+)$#', $this->cover_image_url, $matches)) {
                    $coverImage = $matches[1];
                } else {
                    $coverImage = str_replace(Storage::url(''), '', $this->cover_image_url);
                    $coverImage = ltrim($coverImage, '/');
                }
                
                Log::info('Cover image set from media library for draft', [
                    'cover_image_url' => $this->cover_image_url,
                    'cover_image_path' => $coverImage,
                    'user_id' => auth()->id(),
                ]);
            }

            $postData = [
                'created_by' => auth()->id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'sub_title' => $validated['sub_title'],
                'slug' => $validated['slug'],
                'content' => $validated['content'],
                'cover_image' => $coverImage,
                'is_featured' => $this->is_featured,
                'is_breaking' => $this->is_breaking,
                'status' => 'draft',
                'published_at' => null,
                ...$this->seoData(),
            ];

            if ($this->temporaryPost) {
                $this->temporaryPost->update($postData);
                $post = $this->temporaryPost;
            } else {
                $post = Post::create($postData);
            }

            // Sync authors
            $post->users()->sync($this->user_ids);
            $post->extraCategories()->sync(array_diff($this->extra_categories, [$this->category_id]));

            Log::info('Draft saved', [
                'post_id' => $post->id,
                'title' => $post->title,
                'cover_image' => $post->cover_image,
                'created_by' => auth()->user()->name . ' (ID: ' . auth()->id() . ')',
                'authors' => User::whereIn('id', $this->user_ids)->pluck('name', 'id')->toArray(),
            ]);

            if ($coverImage) {
                Media::where('url', Storage::url($coverImage))
                    ->update([
                        'mediable_id' => $post->id,
                        'mediable_type' => Post::class,
                    ]);
            }

            $imageUrls = $this->extractImageUrls($validated['content']);
            if (!empty($imageUrls)) {
                foreach ($imageUrls as $imageUrl) {
                    Media::where('url', $imageUrl)
                        ->update([
                            'mediable_id' => $post->id,
                            'mediable_type' => Post::class,
                        ]);
                }
            }

            if (!empty($this->tags)) {
                $post->tags()->sync($this->tags);
            } else {
                $post->tags()->detach();
            }

            session()->put('temporary_post_id', $post->id);
            session()->flash('message', 'Draft saved successfully.');
            $this->dispatch('resetEditor');
            
            return redirect()->route('admin.posts.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Draft save failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Failed to save draft: ' . $e->getMessage());
        }
    }

    public function deleteDraft()
    {
        if (!$this->temporaryPost) {
            session()->flash('error', 'No draft selected to delete.');
            return;
        }

        try {
            if (!auth()->user()->hasPermissionTo('delete content', 'web') && 
                $this->temporaryPost->created_by !== auth()->id()) {
                Log::warning('Unauthorized draft deletion attempt', [
                    'user_id' => auth()->id(),
                    'draft_id' => $this->temporaryPost->id,
                ]);
                session()->flash('error', 'You are not authorized to delete this draft.');
                return redirect()->route('admin.posts.index');
            }
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission delete content does not exist for web guard', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);
            session()->flash('error', 'Permission configuration error. Please contact the administrator.');
            return redirect()->route('admin.posts.index');
        }

        try {
            $postId = $this->temporaryPost->id;
            $this->temporaryPost->delete();

            Log::info('Draft deleted', [
                'post_id' => $postId,
                'user_id' => auth()->id(),
            ]);

            session()->flash('message', 'Draft deleted successfully.');
            session()->forget('temporary_post_id');
            return redirect()->route('admin.posts.index');
        } catch (\Exception $e) {
            Log::error('Draft deletion failed: ' . $e->getMessage(), [
                'exception' => $e,
                'post_id' => $this->temporaryPost->id,
            ]);
            session()->flash('error', 'Failed to delete draft: ' . $e->getMessage());
        }
    }

    public function uploadEditorImage()
    {
        $this->validate([
            'editor_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $folder = 'media/editor/' . now()->format('Y/m');
            $originalName = $this->editor_image->getClientOriginalName();
            
            // Get unique filename preserving original name
            $finalName = $this->getUniqueFilename($originalName, $folder, $this->editor_image->extension());
            $path = $this->editor_image->storeAs($folder, $finalName, 'public');
            $url = Storage::url($path);

            Media::create([
                'filename' => $finalName,
                'original_name' => $originalName,
                'mime_type' => $this->editor_image->getMimeType(),
                'size' => $this->editor_image->getSize(),
                'url' => $url,
                'mediable_id' => $this->temporaryPost ? $this->temporaryPost->id : 0,
                'mediable_type' => Post::class,
                'img_desc' => '',
            ]);

            Log::info('Editor image uploaded', [
                'path' => $path,
                'original_name' => $originalName,
                'final_name' => $finalName,
                'url' => $url,
                'user_id' => auth()->id(),
            ]);

            return response()->json(['location' => $url]);
        } catch (\Exception $e) {
            Log::error('Editor image upload failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Upload failed'], 500);
        }
    }

    protected function extractImageUrls($content)
    {
        if (empty($content)) {
            return [];
        }
        preg_match_all('/<img[^>]+src=["\'](.*?)["\']/i', $content, $matches);
        return array_filter($matches[1] ?? []);
    }

    public function render()
    {
        return view('livewire.admin.posts.create');
    }
}