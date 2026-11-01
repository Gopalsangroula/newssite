<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
       protected $fillable = ['post_id', 'author_name', 'author_email', 'body'];

    // A Comment belongs to a Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    /** @use HasFactory<\Database\Factories\CommentFactory> */
     public function user()
    {
        return $this->belongsTo(User::class);
    }
    use HasFactory;
}
