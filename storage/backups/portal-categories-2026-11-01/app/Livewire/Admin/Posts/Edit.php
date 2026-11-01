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

#[Title('Edit Post')]
class Edit extends Component
{
    use WithFileUploads, HasSeoFields;

    public $title;
    public $sub_title;
    public $slug;
    public $category_id;
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

    public $categories;
    public $allTags;
    public $users;

    public function mount($id)
    {
        if (!auth()->check()) {
            session()->flash('error', 'You must be logged in to edit posts.');
            return redirect()->route('admin.posts.index');
        }

        try {
            if (!auth()->user()->hasPermissionTo('edit content', 'web')) {
                Log::warning('Unauthorized post edit attempt: Missing edit content permission', [
                    'user_id' => auth()->id(),
                    'post_id' => $id,
                ]);
                session()->flash('error', 'You are not authorized to edit posts.');
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

        $this->categories = Category::with('children')->whereNull('parent_id')->get();
        $this->allTags = Tag::all();
        $this->users = User::select('id', 'name')->get();

        $this->temporaryPost = Post::findOrFail($id);

        if ($this->temporaryPost) {
            if (!auth()->user()->hasPermissionTo('edit content', 'web') && 
                $this->temporaryPost->created_by !== auth()->id()) {
                Log::warning('Unauthorized post edit attempt', [
                    'user_id' => auth()->id(),
                    'post_id' => $id,
                ]);
                session()->flash('error', 'You are not authorized to edit this post.');
                return redirect()->route('admin.posts.index');
            }

            $this->title = $this->temporaryPost->title;
            $this->sub_title = $this->temporaryPost->sub_title;
            $this->slug = $this->temporaryPost->slug;
            $this->category_id = $this->temporaryPost->category_id;
            $this->content = $this->temporaryPost->content;
            $this->cover_image_url = $this->temporaryPost->cover_image ? Storage::url($this->temporaryPost->cover_image) : null;
            $this->is_featured = $this->temporaryPost->is_featured;
            $this->is_breaking = $this->temporaryPost->is_breaking;
            $this->status = $this->temporaryPost->status;
            $this->published_at = $this->temporaryPost->published_at ? $this->temporaryPost->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            $this->tags = $this->temporaryPost->tags->pluck('id')->toArray();
            $this->user_ids = $this->temporaryPost->users->pluck('id')->toArray();
            $this->fillSeo($this->temporaryPost);
        } else {
            Log::warning('Post not found', [
                'user_id' => auth()->id(),
                'post_id' => $id,
            ]);
            session()->flash('error', 'Post not found.');
            return redirect()->route('admin.posts.index');
        }
    }

    protected function rules()
    {
        return [
            'title' => 'string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . $this->temporaryPost->id,
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
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
            'slug' => 'nullable|string|unique:posts,slug,' . $this->temporaryPost->id,
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
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

        $this->cover_image_url = $url;
        $this->cover_image = null;
        $this->validateOnly('cover_image_url');
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
                while (Post::where('slug', $slug)->where('id', '!=', $this->temporaryPost->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $validated['slug'] = $slug;
            }

            $coverImage = $this->temporaryPost->cover_image;
            if ($this->cover_image) {
                $folder = 'media/' . now()->format('Y/m');
                $originalName = $this->cover_image->getClientOriginalName();
                $filename = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = $this->cover_image->getClientOriginalExtension();
                $baseName = Str::slug($filename);
                $finalName = $baseName . '.' . $extension;

                $counter = 1;
                while (Storage::disk('public')->exists("$folder/$finalName")) {
                    $finalName = $baseName . '-' . $counter++ . '.' . $extension;
                }

                $coverPath = $this->cover_image->storeAs($folder, $finalName, 'public');
                $coverImage = $coverPath;

                $media = Media::create([
                    'filename' => $finalName,
                    'original_name' => $originalName,
                    'mime_type' => $this->cover_image->getMimeType(),
                    'size' => $this->cover_image->getSize(),
                    'url' => Storage::url($coverPath),
                    'mediable_id' => $this->temporaryPost->id,
                    'mediable_type' => Post::class,
                    'img_desc' => '',
                ]);

                Log::info('New cover image uploaded and saved to Media', [
                    'path' => $coverPath,
                    'media_id' => $media->id,
                    'user_id' => auth()->id(),
                ]);
            } elseif ($this->cover_image_url) {
                $relativePath = str_replace(rtrim(Storage::url(''), '/'), '', $this->cover_image_url);
                $relativePath = ltrim($relativePath, '/');
                $media = Media::where('url', $this->cover_image_url)->first();
                if ($media) {
                    $coverImage = $relativePath;
                    Log::info('Cover image set from media library', ['relative_path' => $relativePath, 'user_id' => auth()->id()]);
                } else {
                    Log::warning('Media not found for cover_image_url', ['url' => $this->cover_image_url, 'user_id' => auth()->id()]);
                    $this->addError('cover_image_url', 'Selected media not found in the library.');
                    return;
                }
            }

            $publishedAt = null;
            if ($validated['status'] === 'published') {
                // Editing a published post keeps its original date, so it stays in place on the site.
                $publishedAt = $this->temporaryPost->status === 'published'
                    ? ($this->temporaryPost->published_at ?? now())
                    : now();
            } elseif ($validated['status'] === 'scheduled') {
                $publishedAt = Carbon::parse($this->published_at);
            }

            $postData = [
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

            $this->temporaryPost->update($postData);
            $post = $this->temporaryPost;

            // Sync authors
            $post->users()->sync($this->user_ids);

            Log::info('Post updated', [
                'post_id' => $post->id,
                'title' => $post->title,
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
                Media::whereIn('url', $imageUrls)
                    ->update([
                        'mediable_id' => $post->id,
                        'mediable_type' => Post::class,
                    ]);
            }

            if (!empty($this->tags)) {
                $post->tags()->sync($this->tags);
            } else {
                $post->tags()->detach();
            }

            session()->flash('message', 'Post updated successfully.');
            return redirect()->route('admin.posts.index');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Post update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Failed to update post: ' . $e->getMessage());
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
                while (Post::where('slug', $slug)->where('id', '!=', $this->temporaryPost->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $validated['slug'] = $slug;
            }

            $coverImage = $this->temporaryPost->cover_image;

            if ($this->cover_image) {
                $folder = 'media/' . now()->format('Y/m');
                $originalName = $this->cover_image->getClientOriginalName();
                $filename = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = $this->cover_image->getClientOriginalExtension();
                $baseName = Str::slug($filename);
                $finalName = $baseName . '.' . $extension;

                $counter = 1;
                while (Storage::disk('public')->exists("$folder/$finalName")) {
                    $finalName = $baseName . '-' . $counter++ . '.' . $extension;
                }

                $coverPath = $this->cover_image->storeAs($folder, $finalName, 'public');
                $coverImage = $coverPath;

                $media = Media::create([
                    'filename' => $finalName,
                    'original_name' => $originalName,
                    'mime_type' => $this->cover_image->getMimeType(),
                    'size' => $this->cover_image->getSize(),
                    'url' => Storage::url($coverPath),
                    'mediable_id' => $this->temporaryPost->id,
                    'mediable_type' => Post::class,
                    'img_desc' => '',
                ]);

                Log::info('New cover image uploaded and saved to Media for draft', [
                    'path' => $coverPath,
                    'media_id' => $media->id,
                    'user_id' => auth()->id(),
                ]);
            } elseif ($this->cover_image_url) {
                $relativePath = str_replace(rtrim(Storage::url(''), '/'), '', $this->cover_image_url);
                $relativePath = ltrim($relativePath, '/');
                $media = Media::where('url', $this->cover_image_url)->first();
                if ($media) {
                    $coverImage = $relativePath;
                    Log::info('Cover image set from media library for draft', [
                        'relative_path' => $relativePath,
                        'user_id' => auth()->id()
                    ]);
                } else {
                    Log::warning('Media not found for cover_image_url', [
                        'url' => $this->cover_image_url,
                        'user_id' => auth()->id()
                    ]);
                    $this->addError('cover_image_url', 'Selected media not found in the library.');
                    return;
                }
            }

            $postData = [
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

            $this->temporaryPost->update($postData);
            $post = $this->temporaryPost;

            // Sync authors
            $post->users()->sync($this->user_ids);

            Log::info('Draft saved', [
                'post_id' => $post->id,
                'title' => $post->title,
                'sub_title' => $post->sub_title,
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
                Media::whereIn('url', $imageUrls)
                    ->update([
                        'mediable_id' => $post->id,
                        'mediable_type' => Post::class,
                    ]);
            }

            if (!empty($this->tags)) {
                $post->tags()->sync($this->tags);
            } else {
                $post->tags()->detach();
            }

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

    public function deletePost()
    {
        if (!$this->temporaryPost) {
            session()->flash('error', 'No post selected to delete.');
            return;
        }

        try {
            if (!auth()->user()->hasPermissionTo('delete content', 'web') && 
                $this->temporaryPost->created_by !== auth()->id()) {
                Log::warning('Unauthorized post deletion attempt', [
                    'user_id' => auth()->id(),
                    'post_id' => $this->temporaryPost->id,
                ]);
                session()->flash('error', 'You are not authorized to delete this post.');
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

            Log::info('Post deleted', [
                'post_id' => $postId,
                'user_id' => auth()->id(),
            ]);

            session()->flash('message', 'Post deleted successfully.');
            return redirect()->route('admin.posts.index');
        } catch (\Exception $e) {
            Log::error('Post deletion failed: ' . $e->getMessage(), [
                'exception' => $e,
                'post_id' => $this->temporaryPost->id,
            ]);
            session()->flash('error', 'Failed to delete post: ' . $e->getMessage());
        }
    }

    public function uploadEditorImage()
    {
        $this->validate([
            'editor_image' => 'required|image|max:2048',
        ]);

        try {
            $folder = 'media/editor/' . now()->format('Y/m');
            $originalName = $this->editor_image->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $this->editor_image->getClientOriginalExtension();
            $baseName = Str::slug($filename);
            $finalName = $baseName . '.' . $extension;

            $counter = 1;
            while (Storage::disk('public')->exists("$folder/$finalName")) {
                $finalName = $baseName . '-' . $counter++ . '.' . $extension;
            }

            $path = $this->editor_image->storeAs($folder, $finalName, 'public');
            $url = Storage::url($path);

            Media::create([
                'filename' => $finalName,
                'original_name' => $originalName,
                'mime_type' => $this->editor_image->getMimeType(),
                'size' => $this->editor_image->getSize(),
                'url' => $url,
                'mediable_id' => $this->temporaryPost->id,
                'mediable_type' => Post::class,
                'img_desc' => '',
            ]);

            Log::info('Editor image uploaded', [
                'path' => $path,
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
        return view('livewire.admin.posts.edit');
    }
}