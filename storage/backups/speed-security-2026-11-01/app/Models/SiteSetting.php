<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class SiteSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * All saved settings. Empty until the table exists, so public pages keep
     * working on a deploy whose migration has not run yet.
     */
    public static function values(): array
    {
        return once(function () {
            try {
                return static::pluck('value', 'key')->all();
            } catch (QueryException) {
                return [];
            }
        });
    }
}
