<?php

namespace App\Livewire\Admin\Media;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use Illuminate\Support\Str;

class MediaUploadHandler extends Component
{
    use WithFileUploads;

    public $uploads = [];
    public $uploadProgress = 0;

    protected $rules = [
        'uploads.*' => 'required|file|max:5120|mimes:jpeg,png,jpg,webp,gif',
    ];

    protected $messages = [
        'uploads.*.required' => 'Please select a file to upload.',
        'uploads.*.max' => 'File size must not exceed 5MB.',
        'uploads.*.mimes' => 'Only JPEG, PNG, WEBP, and GIF images are allowed.',
    ];

    public function updatedUploads()
    {
        $this->validate();

        try {
            $totalFiles = count($this->uploads);
            $uploadedCount = 0;

            foreach ($this->uploads as $file) {
                $result = $this->processUpload($file);
                $uploadedCount++;
                $this->uploadProgress = round(($uploadedCount / $totalFiles) * 100);
                $this->dispatch('upload-progress', progress: $this->uploadProgress);
                
                if ($result) {
                    $this->dispatch('file-uploaded', file: $result);
                }
            }

            $this->reset(['uploads', 'uploadProgress']);
            $this->dispatch('upload-complete');
            
        } catch (\Exception $e) {
            Log::error('Bulk upload failed', ['error' => $e->getMessage()]);
            $this->dispatch('upload-error', error: $e->getMessage());
        }
    }

    protected function processUpload($file)
    {
        try {
            $folder = 'media/' . now()->format('Y/m');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $finalName = $this->getUniqueFilename($folder, $baseName . '.' . $extension);
            
            $path = $file->storeAs($folder, $finalName, 'public');
            $url = Storage::url($path);

            $media = Media::create([
                'filename' => $finalName,
                'original_name' => $originalName,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => $url,
                'file_type' => 'image',
                'img_desc' => pathinfo($originalName, PATHINFO_FILENAME),
            ]);

            return [
                'id' => $media->id,
                'url' => $url,
                'name' => $originalName,
            ];
            
        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            return null;
        }
    }

    protected function getUniqueFilename($folder, $filename)
    {
        $counter = 1;
        $pathInfo = pathinfo($filename);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        
        while (Storage::disk('public')->exists("$folder/$filename")) {
            $filename = $name . '-' . $counter++ . ($extension ? '.' . $extension : '');
        }
        
        return $filename;
    }

    public function render()
    {
        return view('livewire.admin.media.media-upload-handler');
    }
}