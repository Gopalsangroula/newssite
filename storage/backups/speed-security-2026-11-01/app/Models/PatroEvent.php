<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatroEvent extends Model
{
    // An event deleted in the admin stays in the table, so that the next import does not bring it back.
    use SoftDeletes;

    protected $fillable = ['uid', 'event_date', 'name', 'is_holiday'];

    protected $casts = [
        'event_date' => 'date',
        'is_holiday' => 'boolean',
    ];
}
