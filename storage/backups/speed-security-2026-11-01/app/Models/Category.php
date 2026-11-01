<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * This category and the ones directly under it, so that a parent page lists them all.
     */
    public function familyIds(): array
    {
        return [$this->id, ...$this->children()->pluck('id')];
    }

    /**
     * The categories under "news", for the site menus. A section added in the admin appears by itself.
     */
    public static function newsSections()
    {
        return once(fn () => static::whereIn('parent_id', static::where('slug', 'news')->select('id'))
            ->orderBy('id')
            ->get(['id', 'name', 'slug']));
    }
}
