<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    // The type filters in the admin, keyed by the value getKindAttribute() returns.
    public const KINDS = [
        'image' => 'Images',
        'video' => 'Videos',
        'audio' => 'Audio',
        'document' => 'PDFs and documents',
    ];

    protected $fillable = [
        'img_desc',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'url',
        'mediable_id',
        'mediable_type',
    ];

    // Polymorphic relationship
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    // Accessor for formatted size
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // image, video, audio, document or other, read from mime_type because
    // file_type is not reliable: Media::create() drops it, so it stays 'other'.
    public function getKindAttribute(): string
    {
        $group = strstr((string) $this->mime_type, '/', true);

        return match ($group) {
            'image', 'video', 'audio' => $group,
            'application', 'text' => 'document',
            default => 'other',
        };
    }

    public function scopeOfKind(Builder $query, string $kind): Builder
    {
        if ($kind === 'document') {
            return $query->where(function ($query) {
                $query->where('mime_type', 'like', 'application/%')
                      ->orWhere('mime_type', 'like', 'text/%');
            });
        }

        return $query->where('mime_type', 'like', $kind . '/%');
    }

    // Path of the file on the public disk. The url decides: a replaced file sits
    // in the folder of the month it was replaced in, not the month of created_at.
    public function getDiskPathAttribute(): string
    {
        $path = (string) parse_url((string) $this->url, PHP_URL_PATH);

        if (str_starts_with($path, '/storage/')) {
            return substr($path, strlen('/storage/'));
        }

        return 'media/' . $this->created_at?->format('Y/m') . '/' . $this->filename;
    }

    public function deleteWithFile(): void
    {
        Storage::disk('public')->delete($this->disk_path);

        $this->delete();
    }
}
