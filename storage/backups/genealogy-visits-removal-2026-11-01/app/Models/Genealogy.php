<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Genealogy extends Model
{
    use HasFactory;

    protected $fillable = [
        'pustanumber',
        'name',
        'sex',
        'date_of_birth',
        'date_of_death',
        'image',
        'description',
        'father_id',
        'mother_name',
        'wife_name',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_death' => 'date',
        'pustanumber' => 'integer',
    ];

    protected $appends = [
        'age',
        'is_living',
        'avatar_url',
        'children_count',
        'spouse_name',
        'life_status',
    ];

    // SCOPES
    public function scopeMale($query)
    {
        return $query->where('sex', 'male');
    }

    public function scopeFemale($query)
    {
        return $query->where('sex', 'female');
    }

    public function scopeLiving($query)
    {
        return $query->whereNull('date_of_death');
    }

    public function scopeDeceased($query)
    {
        return $query->whereNotNull('date_of_death');
    }

    public function scopeWithChildren($query)
    {
        return $query->with(['children' => function ($query) {
            $query->orderBy('date_of_birth');
        }]);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('pustanumber', 'like', "%{$searchTerm}%")
              ->orWhere('wife_name', 'like', "%{$searchTerm}%")
              ->orWhere('mother_name', 'like', "%{$searchTerm}%");
        });
    }

    // RELATIONSHIPS
    public function father(): BelongsTo
    {
        return $this->belongsTo(Genealogy::class, 'father_id')->withDefault([
            'name' => 'अज्ञात पिता',
            'pustanumber' => '०'
        ]);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Genealogy::class, 'father_id')->orderBy('date_of_birth');
    }

    // COMPUTED PROPERTIES
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) return null;
        
        $endDate = $this->date_of_death ?: now();
        return $this->date_of_birth->diffInYears($endDate);
    }

    public function getIsLivingAttribute(): bool
    {
        return is_null($this->date_of_death);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->image && Storage::exists('public/' . $this->image)) {
            return Storage::url($this->image);
        }

        return $this->sex === 'female' 
            ? asset('images/default-female-avatar.png')
            : asset('images/default-male-avatar.png');
    }

    public function getChildrenCountAttribute(): int
    {
        return $this->children()->count();
    }

    public function getSpouseNameAttribute(): ?string
    {
        return $this->wife_name;
    }

    public function getLifeStatusAttribute(): string
    {
        return $this->is_living ? 'जीवित' : 'स्वर्गीय';
    }

    public function getGenerationalLevelAttribute(): int
    {
        $level = 1;
        $person = $this;

        while ($person->father) {
            $level++;
            $person = $person->father;
        }

        return $level;
    }

    // METHODS
    public function getFamilyTree(int $levels = 3): array
    {
        return [
            'current' => $this,
            'parents' => [
                'father' => $this->father,
                'mother_name' => $this->mother_name,
            ],
            'children' => $this->children,
            'ancestors' => $this->getAncestors($levels),
        ];
    }

    public function getAncestors(int $generations = 5): array
    {
        $ancestors = [];
        $current = $this;
        $level = 0;

        while ($current->father && $level < $generations) {
            $ancestors[] = [
                'person' => $current->father,
                'relationship' => $this->getRelationshipTerm($level + 1)
            ];
            $current = $current->father;
            $level++;
        }

        return $ancestors;
    }

    protected function getRelationshipTerm(int $level): string
    {
        return match ($level) {
            1 => 'पिता',
            2 => 'बाबा (हजुरबुवा)',
            3 => 'परबाबा (बडबाबा)',
            4 => 'परपरबाबा',
            default => 'पूर्वज',
        };
    }

    // VALIDATION - REMOVED UNIQUE VALIDATION
    public static function validationRules($id = null): array
    {
        return [
            'pustanumber' => 'required|integer|min:0', // Removed unique validation
            'name' => 'required|string|max:255',
            'sex' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'date_of_death' => 'nullable|date|after:date_of_birth',
            'father_id' => 'nullable|exists:genealogies,id',
            'mother_name' => 'nullable|string|max:255',
            'wife_name' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ];
    }

    // EVENTS - REMOVED UNIQUE CHECK IN BOOT METHOD
    protected static function boot()
    {
        parent::boot();

        // Removed the unique check in creating event
        static::updating(function ($genealogy) {
            if ($genealogy->father_id == $genealogy->id) {
                throw new \Exception('व्यक्ति आफैंको पिता हुन सक्दैन।');
            }
        });
    }

    // public function getRouteKeyName()
    // {
    //     return 'pustanumber';
    // }
}