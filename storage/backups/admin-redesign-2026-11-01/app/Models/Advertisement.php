<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Advertisement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'placement',
        'content',
        'image_url',
        'target_url',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate unique slug from title if not provided
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $baseSlug = Str::slug($model->title);
                $slug = $baseSlug;
                $count = 1;

                // Ensure slug is unique
                while (static::where('slug', $slug)->where('id', '!=', $model->id)->exists()) {
                    $slug = "$baseSlug-$count";
                    $count++;
                }

                $model->slug = $slug;
            }
        });
    }

    /**
     * Scope to retrieve currently active advertisements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrentlyActive($query)
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope to retrieve advertisements by placement.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $placement
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPlacement($query, ?string $placement)
    {
        return $query->where('placement', $placement);
    }

    /**
     * Check if the advertisement is currently active.
     *
     * @return bool
     */
    public function isCurrentlyActive(): bool
    {
        return static::currentlyActive()->where('id', $this->id)->exists();
    }

    /**
     * Get the full image URL.
     *
     * @return string|null
     */
    public function getFullImageUrlAttribute(): ?string
    {
        if ($this->image_url && !Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return asset('storage/' . $this->image_url);
        }

        return $this->image_url;
    }

    /**
     * Validate advertisement data based on type.
     *
     * @return bool
     */
    public function isValidForType(): bool
    {
        if ($this->type === 'image' && empty($this->image_url)) {
            return false;
        }

        if ($this->type === 'script' && empty($this->content)) {
            return false;
        }

        return true;
    }

    /**
     * Set the starts_at attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setStartsAtAttribute($value)
    {
        $this->attributes['starts_at'] = $value ? Carbon::parse($value) : null;
    }

    /**
     * Set the ends_at attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setEndsAtAttribute($value)
    {
        $this->attributes['ends_at'] = $value ? Carbon::parse($value) : null;
    }
}