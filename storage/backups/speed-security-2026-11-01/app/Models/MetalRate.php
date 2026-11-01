<?php

namespace App\Models;

use App\Support\NepaliDate;
use Illuminate\Database\Eloquent\Model;

class MetalRate extends Model
{
    /**
     * The rate columns and their public names. Rates are whole rupees per tola.
     */
    public const METALS = [
        'fine_gold' => 'छापावाल सुन',
        'tejabi_gold' => 'तेजाबी सुन',
        'silver' => 'चाँदी',
    ];

    private const GRAMS_PER_TOLA = 11.664;

    protected $fillable = ['rate_date', 'fine_gold', 'tejabi_gold', 'silver'];

    protected $casts = [
        'rate_date' => 'date',
    ];

    /**
     * The price of 10 grams at a rate per tola.
     */
    public static function per10Grams(int $perTola): int
    {
        return (int) round($perTola * 10 / self::GRAMS_PER_TOLA);
    }

    /**
     * रु. १,७८,५००, with the commas where Nepali readers expect them.
     */
    public static function rupees(int $amount): string
    {
        return 'रु. '.NepaliDate::digits(preg_replace('/(\d)(?=(\d\d)+\d$)/', '$1,', (string) $amount));
    }
}
