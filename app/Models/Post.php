<?php

namespace App\Models;

use App\Support\Seo;
use App\Support\VideoLink;
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
        'kicker',
        'excerpt',
        'slug',
        'cover_image',
        'video_url',
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
     * What a listing prints, apart from the opening of the story.
     */
    private const LISTING_COLUMNS = [
        'id', 'created_by', 'published_by', 'category_id', 'title', 'sub_title', 'kicker', 'excerpt', 'slug', 'cover_image',
        'video_url', 'is_featured', 'is_breaking', 'status', 'views', 'published_at', 'created_at', 'updated_at',
    ];

    /**
     * Words the full-text index leaves out. Asking for one of them would find nothing at all.
     */
    private const UNINDEXED_WORDS = [
        'about', 'are', 'com', 'for', 'from', 'how', 'that', 'the', 'this', 'und', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'www',
    ];

    private const SHORTEST_INDEXED_WORD = 3;

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
     * Scope for posts filed in any of these categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param iterable<int> $categoryIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategories($query, $categoryIds)
    {
        return $query->whereIn('category_id', $categoryIds);
    }

    /**
     * Only what a listing needs. Stories can run long and a page lists dozens of them,
     * so a summary is made from the opening of the story rather than all of it.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForListing($query)
    {
        return $query
            ->select(array_map(fn ($column) => 'posts.'.$column, self::LISTING_COLUMNS))
            ->selectRaw('LEFT(posts.content, 2000) AS content');
    }

    /**
     * Scope for posts matching what a reader typed. The full-text index keeps this fast however
     * many stories there are. Words too short for that index are looked for in titles only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $term)
    {
        $words = preg_split('/[^\p{L}\p{M}\p{N}]+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $indexed = array_filter($words, fn ($word) => mb_strlen($word) >= self::SHORTEST_INDEXED_WORD
            && ! in_array(mb_strtolower($word), self::UNINDEXED_WORDS, true));

        if ($indexed === []) {
            return $query->where('title', 'like', '%'.addcslashes($term, '%_\\').'%');
        }

        // Every word must appear, and "लिबाङ" also finds "लिबाङको".
        $match = implode(' ', array_map(fn ($word) => '+'.$word.'*', $indexed));

        return $query->whereRaw('MATCH(posts.title, posts.sub_title, posts.content) AGAINST(? IN BOOLEAN MODE)', [$match]);
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
     * The summary under a headline. An excerpt written in the editor is printed as it is; without one,
     * the opening of the story is cut to $limit characters. Many posts open by repeating the title,
     * and some have nothing else.
     */
    public function summary(int $limit): string
    {
        $excerpt = trim((string) $this->excerpt);

        if ($excerpt !== '') {
            return $excerpt;
        }

        $text = trim(preg_replace('~https?://\S+~u', '', Seo::plainText($this->sub_title ?: $this->content)));
        $title = Seo::plainText($this->title);

        return Str::limit(Str::startsWith($text, $title)
            ? preg_replace('/^[\s,।–—-]+/u', '', Str::after($text, $title))
            : $text, $limit);
    }

    /**
     * The YouTube or Facebook video that plays in place of the featured image, if there is one.
     */
    public function getVideoAttribute(): ?array
    {
        return VideoLink::parse($this->video_url);
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