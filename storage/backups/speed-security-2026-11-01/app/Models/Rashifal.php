<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rashifal extends Model
{
    /**
     * The 12 signs in order, with the first letters of the names that belong to each.
     */
    public const SIGNS = [
        'mesh' => ['name' => 'मेष', 'symbol' => '♈', 'letters' => 'चु, चे, चो, ला, लि, लु, ले, लो, अ'],
        'vrish' => ['name' => 'वृष', 'symbol' => '♉', 'letters' => 'इ, उ, ए, ओ, वा, वि, वु, वे, वो'],
        'mithun' => ['name' => 'मिथुन', 'symbol' => '♊', 'letters' => 'का, कि, कु, घ, ङ, छ, के, को, हा'],
        'karkat' => ['name' => 'कर्कट', 'symbol' => '♋', 'letters' => 'हि, हु, हे, हो, डा, डि, डु, डे, डो'],
        'singha' => ['name' => 'सिंह', 'symbol' => '♌', 'letters' => 'मा, मि, मु, मे, मो, टा, टि, टु, टे'],
        'kanya' => ['name' => 'कन्या', 'symbol' => '♍', 'letters' => 'टो, पा, पि, पु, ष, ण, ठ, पे, पो'],
        'tula' => ['name' => 'तुला', 'symbol' => '♎', 'letters' => 'रा, रि, रु, रे, रो, ता, ति, तु, ते'],
        'vrishchik' => ['name' => 'वृश्चिक', 'symbol' => '♏', 'letters' => 'तो, ना, नि, नु, ने, नो, या, यि, यु'],
        'dhanu' => ['name' => 'धनु', 'symbol' => '♐', 'letters' => 'ये, यो, भा, भि, भु, धा, फा, ढा, भे'],
        'makar' => ['name' => 'मकर', 'symbol' => '♑', 'letters' => 'भो, जा, जि, जु, जे, जो, ख, खि, खु, खे, खो, गा, गि'],
        'kumbha' => ['name' => 'कुम्भ', 'symbol' => '♒', 'letters' => 'गु, गे, गो, सा, सि, सु, से, सो, दा'],
        'meen' => ['name' => 'मीन', 'symbol' => '♓', 'letters' => 'दि, दु, थ, झ, ञ, दे, दो, चा, चि'],
    ];

    protected $fillable = ['rashifal_date', 'author', 'readings'];

    protected $casts = [
        'rashifal_date' => 'date',
        'readings' => 'array',
    ];
}
