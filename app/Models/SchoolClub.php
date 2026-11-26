<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClub extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'price',
        'currency',
        'billing_period',
        'capacity',
        'location',
        'schedule',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'coach_name',
        'coach_email',
        'coach_phone',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'capacity' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_start' => 'date',
            'registration_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'school_club_student')
            ->withTimestamps();
    }

    public function getAvailableSpotsAttribute(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->students()->count());
    }

    public function getIsRegistrationOpenAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->registration_start && $today->lt($this->registration_start)) {
            return false;
        }

        if ($this->registration_end && $today->gt($this->registration_end)) {
            return false;
        }

        return $this->available_spots === null || $this->available_spots > 0;
    }
}
