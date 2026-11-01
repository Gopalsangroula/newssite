<?php

namespace App\Livewire\Admin\Media;

use App\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The upload area. It sits in the media library and on the Add Media page, and
 * tells its parent about new files with the upload-complete event.
 */
class Uploader extends Component
{
    use WithFileUploads;

    public $files = [];
    public $previews = [];
    public $customFilenames = [];
    public $imgDescriptions = [];
    public $uploadErrors = [];

    protected $rules = [
        'files.*' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,mp4,webm,mp3',
        'customFilenames.*' => 'nullable|string|max:200|regex:/^[\w\-. ]+$/',
        'imgDescriptions.*' => 'nullable|string|max:255',
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

    public function hydrate()
    {
        foreach ($this->files as $index => $file) {
            if (isset($this->previews[$index]) && $this->isImage($file)) {
                $this->previews[$index] = $file->temporaryUrl();
            }
        }
    }

    public function updatedFiles()
    {
        $this->uploadErrors = [];

        try {
            $this->validateOnly('files.*');
            $this->previews = [];
            $this->customFilenames = [];
            $this->imgDescriptions = [];
            foreach ($this->files as $index => $file) {
                $this->previews[$index] = $this->isImage($file) ? $file->temporaryUrl() : null;
                $this->customFilenames[$index] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $this->imgDescriptions[$index] = '';
            }
        } catch (ValidationException $e) {
            $this->uploadErrors = Arr::flatten($e->errors());
            $this->reset(['files', 'previews', 'customFilenames', 'imgDescriptions']);
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
        }
    }

    public function save()
    {
        $this->validate();
        $this->uploadErrors = [];

        try {
            DB::beginTransaction();
            $folder = 'media/' . now()->format('Y/m');
            Storage::disk('public')->makeDirectory($folder, 0775, true, true);
            $mediaRecords = [];

            foreach ($this->files as $index => $file) {
                $extension = $file->getClientOriginalExtension();
                $safeFilename = $this->generateSafeFilename(
                    ($this->customFilenames[$index] ?? '') ?: $file->getClientOriginalName(),
                    $extension
                );
                $safeFilename = $this->ensureUniqueFilename($folder, $safeFilename);
                $path = $file->storeAs($folder, $safeFilename, 'public');

                $mediaRecords[] = [
                    'filename' => $safeFilename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'url' => Storage::url($path),
                    'file_type' => $this->getFileType($extension),
                    'img_desc' => $this->imgDescriptions[$index] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Media::insert($mediaRecords);
            DB::commit();
            $this->reset(['files', 'previews', 'customFilenames', 'imgDescriptions']);
            $this->dispatch('upload-complete', count: count($mediaRecords));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('File upload failed: ' . $e->getMessage());
            $this->uploadErrors[] = 'Failed to upload files: ' . $e->getMessage();
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
        // A name written only in Nepali slugs to nothing, which would leave just ".jpg".
        $filename = Str::slug(pathinfo($name, PATHINFO_FILENAME)) ?: 'file';
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
        return view('partials.uploadmedia');
    }
}
