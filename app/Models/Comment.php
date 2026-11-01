<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
       protected $fillable = ['post_id', 'author_name', 'author_email', 'body', 'is_approved'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    // A Comment belongs to a Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Scope for comments that readers can see.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for comments waiting for approval.
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /** @use HasFactory<\Database\Factories\CommentFactory> */
     public function user()
    {
        return $this->belongsTo(User::class);
    }
    use HasFactory;
}
