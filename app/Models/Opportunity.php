<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'job' => 'Job Opportunity',
        'internship' => 'Internship',
        'scholarship' => 'Scholarship',
        'training' => 'Training Program',
        'volunteer' => 'Volunteer Opportunity',
    ];

    protected $fillable = [
        'title', 'organization', 'opportunity_type', 'description', 'requirements',
        'application_deadline', 'contact_info', 'application_url', 'target_course',
        'target_graduation_year', 'publication_status', 'created_by',
    ];

    protected $casts = ['application_deadline' => 'date'];

    public function applications(): HasMany
    {
        return $this->hasMany(OpportunityApplication::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
