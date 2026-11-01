<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'cover_image',
        'content',
        'status',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the user who created the page.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all media associated with the page (cover + content images).
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the full URL for the cover image.
     *
     * @return string|null
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    /**
     * Scope a query to only include scheduled pages.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('published_at', '>', now());
    }

    /**
     * Scope a query to only include drafts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if the page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               ($this->published_at === null || $this->published_at->lte(now()));
    }

    /**
     * Check if the page is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->published_at?->gt(now());
    }

    /**
     * Check if the page is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}