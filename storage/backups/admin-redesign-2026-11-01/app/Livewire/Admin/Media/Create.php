<?php

namespace App\Livewire\Admin\Media;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithFileUploads;

    public $files = [];
    public $previews = [];
    public $customFilenames = [];
    public $imgDescriptions = []; // New property for image descriptions
    public $uploadProgress = 0;
    public $uploadErrors = [];

    protected $rules = [
        'files.*' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,mp4,webm,mp3',
        'customFilenames.*' => 'nullable|string|max:200|regex:/^[\w\-. ]+$/',
        'imgDescriptions.*' => 'nullable|string|max:255', // Validation for img_desc
    ];

    protected $messages = [
        'files.*.required' => 'Please select a file to upload.',
        'files.*.max' => 'File size must not exceed 20MB.',
        'files.*.mimes' => 'Unsupported file type. Allowed types: images, PDFs, documents, videos, audio.',
        'customFilenames.*.string' => 'Filename must be a valid string.',
        'customFilenames.*.max' => 'Filename must not exceed 200 characters.',
        'customFilenames.*.regex' => 'Filename contains invalid characters.',
        'imgDescriptions.*.string' => 'Image description must be a valid string.',
        'imgDescriptions.*.max' => 'Image description must not exceed 255 characters.',
    ];

    protected $fileTypeCategories = [
        'image' => ['jpg', 'jpeg', 'png', 'gif'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'video' => ['mp4', 'webm'],
        'audio' => ['mp3'],
    ];

    public function mount()
    {
        $this->uploadErrors = [];
        $this->uploadProgress = 0;
        $this->imgDescriptions = []; // Initialize imgDescriptions
    }

    public function hydrate()
    {
        foreach ($this->files as $index => $file) {
            if (isset($this->previews[$index]) && $this->isImage($file)) {
                $this->previews[$index] = $file->temporaryUrl();
            }
        }
    }

    #[On('file-uploaded')]
    public function updatedFiles()
    {
        try {
            $this->validateOnly('files.*');
            $this->previews = [];
            $this->customFilenames = [];
            $this->imgDescriptions = []; // Reset imgDescriptions
            foreach ($this->files as $index => $file) {
                $this->previews[$index] = $this->isImage($file) ? $file->temporaryUrl() : $this->getFileIcon($file->getClientOriginalExtension());
                $this->customFilenames[$index] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $this->imgDescriptions[$index] = ''; // Initialize empty description
            }
        } catch (ValidationException $e) {
            $this->uploadErrors = $e->errors();
            $this->reset(['files', 'previews', 'customFilenames', 'imgDescriptions']);
            $this->dispatch('upload-failed', errors: $this->uploadErrors);
        }
    }

    public function removeFile($index)
    {
        if (isset($this->files[$index])) {
            unset($this->files[$index], $this->previews[$index], $this->customFilenames[$index], $this->imgDescriptions[$index]);
            $this->files = array_values($this->files);
            $this->previews = array_values($this->previews);
            $this->customFilenames = array_values($this->customFilenames);
            $this->imgDescriptions = array_values($this->imgDescriptions);
            $this->dispatch('file-removed', index: $index);
        }
    }

    public function save()
    {
        $this->validate();
        $this->uploadProgress = 0;
        $this->uploadErrors = [];

        try {
            DB::beginTransaction();
            $totalFiles = count($this->files);
            $uploadedCount = 0;
            $folder = 'media/' . now()->format('Y/m');
            Storage::disk('public')->makeDirectory($folder, 0775, true, true);
            $mediaRecords = [];

            foreach ($this->files as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();
                $fileType = $this->getFileType($extension);
                $safeFilename = $this->generateSafeFilename(
                    $this->customFilenames[$index] ?? $originalName,
                    $extension
                );
                $safeFilename = $this->ensureUniqueFilename($folder, $safeFilename);
                $path = $file->storeAs($folder, $safeFilename, 'public');

                $mediaRecords[] = [
                    'filename' => $safeFilename,
                    'original_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'url' => Storage::url($path),
                    'file_type' => $fileType,
                    'img_desc' => $this->imgDescriptions[$index] ?? null, // Add img_desc
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $uploadedCount++;
                $this->uploadProgress = round(($uploadedCount / $totalFiles) * 100);
                $this->dispatch('upload-progress', progress: $this->uploadProgress);
            }

            Media::insert($mediaRecords);
            DB::commit();
            session()->flash('message', 'Files uploaded successfully.');
            $this->reset(['files', 'previews', 'customFilenames', 'imgDescriptions', 'uploadProgress']);
            $this->dispatch('upload-complete');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('File upload failed: ' . $e->getMessage());
            $this->uploadErrors[] = 'Failed to upload files: ' . $e->getMessage();
            $this->dispatch('upload-failed', errors: $this->uploadErrors);
            $this->uploadProgress = 0;
        }
    }

    protected function isImage($file)
    {
        return str_starts_with($file->getMimeType(), 'image/');
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

    #[On('delete-media')]
    public function delete($id)
    {
        try {
            $media = Media::findOrFail($id);
            $folderPath = 'media/' . $media->created_at->format('Y/m') . '/' . $media->filename;
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->delete($folderPath);
            }
            $media->delete();
            session()->flash('message', 'File deleted successfully.');
            $this->dispatch('media-deleted', id: $id);
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete file: ' . $e->getMessage());
            $this->dispatch('delete-failed', error: $e->getMessage());
        }
    }

    public function render()
    {
        $mediaFiles = Media::latest()->paginate(12);
        return view('livewire.admin.media.create', [
            'mediaFiles' => $mediaFiles,
            'formatBytes' => fn($bytes) => $this->formatBytes($bytes),
            'uploadErrors' => $this->uploadErrors,
        ]);
       
    }
}