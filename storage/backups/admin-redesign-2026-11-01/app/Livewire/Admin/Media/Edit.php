<?php

namespace App\Livewire\Admin\Media;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class Edit extends Component
{
    use WithFileUploads;

    public $mediaId;
    public $file;
    public $preview;
    public $customFilename;
    public $imgDescription;
    public $originalName;
    public $mimeType;
    public $size;
    public $uploadError;

    protected $rules = [
        'file' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,mp4,webm,mp3',
        'customFilename' => 'nullable|string|max:200|regex:/^[\w\-. ]+$/',
        'imgDescription' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'file.max' => 'File size must not exceed 20MB.',
        'file.mimes' => 'Unsupported file type. Allowed types: images, PDFs, documents, videos, audio.',
        'customFilename.string' => 'Filename must be a valid string.',
        'customFilename.max' => 'Filename must not exceed 200 characters.',
        'customFilename.regex' => 'Filename contains invalid characters.',
        'imgDescription.string' => 'Image description must be a valid string.',
        'imgDescription.max' => 'Image description must not exceed 255 characters.',
    ];

    protected $fileTypeCategories = [
        'image' => ['jpg', 'jpeg', 'png', 'gif'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'video' => ['mp4', 'webm'],
        'audio' => ['mp3'],
    ];

    public function mount($id)
    {
        if (!auth()->check()) {
            session()->flash('error', 'You must be logged in to edit media.');
            return redirect()->route('admin.media.index');
        }

        try {
            if (!auth()->user()->hasPermissionTo('edit content', 'web')) {
                Log::warning('Unauthorized media edit attempt: Missing edit content permission', [
                    'user_id' => auth()->id(),
                    'media_id' => $id,
                ]);
                session()->flash('error', 'You are not authorized to edit media.');
                return redirect()->route('admin.media.index');
            }
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission edit content does not exist for web guard', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);
            session()->flash('error', 'Permission configuration error. Please contact the administrator.');
            return redirect()->route('admin.media.index');
        }

        $this->mediaId = $id;
        $media = Media::findOrFail($id);

        $this->preview = $media->url;
        $this->customFilename = pathinfo($media->filename, PATHINFO_FILENAME);
        $this->imgDescription = $media->img_desc;
        $this->originalName = $media->original_name;
        $this->mimeType = $media->mime_type;
        $this->size = $media->size;
        $this->uploadError = null;
    }

    public function updatedFile()
    {
        try {
            $this->validateOnly('file');
            $this->preview = $this->isImage($this->file) ? $this->file->temporaryUrl() : $this->getFileIcon($this->file->getClientOriginalExtension());
            $this->customFilename = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
            $this->originalName = $this->file->getClientOriginalName();
            $this->mimeType = $this->file->getMimeType();
            $this->size = $this->file->getSize();
        } catch (ValidationException $e) {
            $this->uploadError = $e->errors()['file'][0];
            $this->reset('file');
            $this->dispatch('upload-failed', error: $this->uploadError);
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $media = Media::findOrFail($this->mediaId);

            if (!auth()->user()->hasPermissionTo('edit content', 'web')) {
                Log::warning('Unauthorized media edit attempt', [
                    'user_id' => auth()->id(),
                    'media_id' => $this->mediaId,
                ]);
                session()->flash('error', 'You are not authorized to edit media.');
                return redirect()->route('admin.media.index');
            }

            $updateData = [
                'img_desc' => $this->imgDescription,
            ];

            if ($this->file) {
                // Delete old file
                $oldFolderPath = 'media/' . $media->created_at->format('Y/m') . '/' . $media->filename;
                if (Storage::disk('public')->exists($oldFolderPath)) {
                    Storage::disk('public')->delete($oldFolderPath);
                }

                // Upload new file
                $folder = 'media/' . now()->format('Y/m');
                Storage::disk('public')->makeDirectory($folder, 0775, true, true);
                $extension = $this->file->getClientOriginalExtension();
                $safeFilename = $this->generateSafeFilename(
                    $this->customFilename ?? $this->file->getClientOriginalName(),
                    $extension
                );
                $safeFilename = $this->ensureUniqueFilename($folder, $safeFilename);
                $path = $this->file->storeAs($folder, $safeFilename, 'public');

                $updateData = array_merge($updateData, [
                    'filename' => $safeFilename,
                    'original_name' => $this->file->getClientOriginalName(),
                    'mime_type' => $this->file->getMimeType(),
                    'size' => $this->file->getSize(),
                    'url' => Storage::url($path),
                    'file_type' => $this->getFileType($extension),
                ]);
            } elseif ($this->customFilename && $this->customFilename !== pathinfo($media->filename, PATHINFO_FILENAME)) {
                // Rename existing file
                $extension = pathinfo($media->filename, PATHINFO_EXTENSION);
                $folder = 'media/' . $media->created_at->format('Y/m');
                $safeFilename = $this->generateSafeFilename($this->customFilename, $extension);
                $safeFilename = $this->ensureUniqueFilename($folder, $safeFilename);
                $oldPath = 'media/' . $media->created_at->format('Y/m') . '/' . $media->filename;
                $newPath = 'media/' . $media->created_at->format('Y/m') . '/' . $safeFilename;

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);
                }

                $updateData['filename'] = $safeFilename;
                $updateData['url'] = Storage::url($newPath);
            }

            $media->update($updateData);

            Log::info('Media updated successfully', [
                'media_id' => $media->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('message', 'Media updated successfully.');
            return redirect()->route('admin.media.index');
        } catch (\Exception $e) {
            Log::error('Media update failed: ' . $e->getMessage(), [
                'media_id' => $this->mediaId,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Failed to update media: ' . $e->getMessage());
        }
    }

    protected function isImage($file)
    {
        return $file && str_starts_with($file->getMimeType(), 'image/');
    }

    protected function getFileType($extension)
    {
        $extension = strtolower($extension);
        foreach ($this->fileTypeCategories as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        return 'other';
    }

    protected function getFileIcon($extension)
    {
        $type = $this->getFileType($extension);
        return match ($type) {
            'image' => asset('icons/image-icon.svg'),
            'document' => asset('icons/document-icon.svg'),
            'video' => asset('icons/video-icon.svg'),
            'audio' => asset('icons/audio-icon.svg'),
            default => asset('icons/file-icon.svg'),
        };
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function generateSafeFilename($name, $extension)
    {
        $filename = Str::slug(pathinfo($name, PATHINFO_FILENAME));
        $filename = substr($filename, 0, 180);
        return $filename . '.' . $extension;
    }

    protected function ensureUniqueFilename($folder, $filename)
    {
        $counter = 1;
        $pathInfo = pathinfo($filename);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        while (Storage::disk('public')->exists("$folder/$filename")) {
            $filename = "{$name}-{$counter}.{$extension}";
            $counter++;
        }
        return $filename;
    }

    public function render()
    {
        return view('livewire.admin.media.edit', [
            'formatBytes' => fn($bytes) => $this->formatBytes($bytes),
        ]);
    }
}