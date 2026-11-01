<?php

namespace App\Models;

use App\Support\Seo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by',
        'published_by',
        'category_id',
        'title',
        'sub_title',
        'slug',
        'cover_image',
        'content',
        'is_featured',
        'is_breaking',
        'status',
        'published_at',
        'seo_title',
        'seo_description',
        'focus_keyword',
        'og_image',
        'noindex',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_breaking' => 'boolean',
        'noindex' => 'boolean',
        'status' => 'string',
    ];

    // Post status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';

    /**
     * Get the user who created the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'name' => 'Deleted User',
            'id' => null,
        ]);
    }

    /**
     * Get the user credited as the publisher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by')->withDefault([
            'name' => 'Deleted User',
            'id' => null,
        ]);
    }

    /**
     * Get all authors of the post (via post_user pivot table).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'post_user', 'post_id', 'user_id');
        
    }

    /**
     * Get the category of the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault([
            'name' => 'Uncategorized',
            'id' => null,
        ]);
    }

    /**
     * Get the tags associated with the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    /**
     * Get the comments for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the media attachments for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the recorded page views of the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    /**
     * Categories beyond the main one, so that a post can show in several sections.
     */
    public function extraCategories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    /**
     * Scope for posts filed in any of these categories, as the main one or an extra one.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param iterable<int> $categoryIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategories($query, $categoryIds)
    {
        return $query->where(function ($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds)
                ->orWhereHas('extraCategories', fn ($query) => $query->whereIn('categories.id', $categoryIds));
        });
    }

    /**
     * Scope for published posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                     ->where(function ($query) {
                         $query->whereNull('published_at')
                               ->orWhere('published_at', '<=', now());
                     });
    }

    /**
     * Scope for draft posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for scheduled posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                     ->where('published_at', '>', now());
    }

    /**
     * Check if the post is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED &&
               ($this->published_at === null || $this->published_at <= now());
    }
    public function getAuthorAttribute()
    {
        return $this->users->first() ?? $this->createdBy;
    }

    /**
     * Plain text for a summary. Many posts open by repeating the title, some have nothing else.
     */
    public function getExcerptAttribute(): string
    {
        $text = trim(preg_replace('~https?://\S+~u', '', Seo::plainText($this->sub_title ?: $this->content)));
        $title = Seo::plainText($this->title);

        return Str::startsWith($text, $title)
            ? preg_replace('/^[\s,।–—-]+/u', '', Str::after($text, $title))
            : $text;
    }

    /**
     * Check if the post is a draft.
     *
     * @return bool
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if the post is scheduled.
     *
     * @return bool
     */
    public function isScheduled()
    {
        return $this->status === self::STATUS_SCHEDULED && $this->published_at > now();
    }
}