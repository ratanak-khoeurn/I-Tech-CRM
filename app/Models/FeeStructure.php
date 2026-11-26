<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    use CampusScoped;

    /** A fee without a campus is a shared default every campus can invoice with. */
    public static function campusScopeIncludesNull(): bool
    {
        return true;
    }

    public const FEE_TYPES = [
        'tuition' => 'Tuition',
        'registration' => 'Registration',
        'examination' => 'Examination',
        'library' => 'Library',
        'uniform' => 'Uniform & Materials',
        'other' => 'Other',
    ];

    public const FREQUENCIES = [
        'one_time' => 'One-time',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'per_semester' => 'Per semester',
        'yearly' => 'Yearly',
    ];

    protected $fillable = [
        'course_id', 'campus_id', 'name', 'fee_type', 'amount', 'frequency', 'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
