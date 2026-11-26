<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFaceProfile extends Model
{
    protected $fillable = [
        'employee_id',
        'embedding',
        'model_version',
        'is_active',
        'consent_obtained_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'encrypted:array',
            'is_active' => 'boolean',
            'consent_obtained_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
