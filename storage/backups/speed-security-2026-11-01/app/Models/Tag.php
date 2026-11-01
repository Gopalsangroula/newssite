<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
     protected $fillable = ['name', 'slug'];

    // Many Tags belong to many Posts (pivot)
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;
}
