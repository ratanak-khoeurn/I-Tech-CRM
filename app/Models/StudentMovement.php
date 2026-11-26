<?php

namespace App\Models;

use App\Support\CampusContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMovement extends Model
{
    public const TYPES = [
        'enrollment' => 'Enrollment',
        'transfer' => 'Transfer',
        'batch_change' => 'Batch Change',
        'campus_change' => 'Campus Change',
        'deferral' => 'Deferral',
        'withdrawal' => 'Withdrawal',
        'reinstatement' => 'Reinstatement',
        'status_change' => 'Status Change',
    ];

    protected $fillable = [
        'student_id', 'movement_type', 'from_batch_id', 'to_batch_id',
        'from_campus_id', 'to_campus_id', 'from_status', 'to_status',
        'effective_date', 'reason', 'notes', 'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    protected static function booted(): void
    {
        // A move is visible when either end belongs to the account's campuses.
        static::addGlobalScope('campus', function (Builder $query) {
            $visible = CampusContext::visibleIds();

            if ($visible !== null) {
                $query->where(function (Builder $w) use ($visible) {
                    $w->whereIn('from_campus_id', $visible)->orWhereIn('to_campus_id', $visible);
                });
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromBatch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'from_batch_id');
    }

    public function toBatch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'to_batch_id');
    }

    public function fromCampus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'from_campus_id');
    }

    public function toCampus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'to_campus_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->movement_type] ?? ucfirst(str_replace('_', ' ', (string) $this->movement_type));
    }
}
