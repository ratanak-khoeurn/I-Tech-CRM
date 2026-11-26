<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Student extends Model
{
    use CampusScoped;
    use HasFactory, SoftDeletes;

    public const STAGE_LABELS = [
        'current_student' => 'Current Student',
        'alumni' => 'Alumni',
        'dropped_out' => 'Dropped Out',
    ];

    public const GRADUATION_STATUS_LABELS = [
        'not_graduated' => 'Not Graduated',
        'graduated' => 'Graduated',
    ];

    public const STATUS_LABELS = [
        'active_alumni' => 'Active Alumni',
        'inactive' => 'Inactive',
        'unreachable' => 'Unreachable',
        'do_not_contact' => 'Do Not Contact',
    ];

    public const PIPELINE_LABELS = [
        'not_contacted' => 'Not Contacted',
        'contacted' => 'Contacted',
        'in_progress' => 'In Progress',
        'enrolled' => 'Enrolled',
        'unreachable' => 'Unreachable',
    ];

    public const PIPELINE_COLORS = [
        'not_contacted' => 'secondary',
        'contacted' => 'info',
        'in_progress' => 'warning',
        'enrolled' => 'success',
        'unreachable' => 'danger',
    ];

    public const EMPLOYMENT_STATUS_LABELS = [
        'employed' => 'Employed',
        'self_employed' => 'Self-employed',
        'unemployed' => 'Unemployed',
        'freelancing' => 'Freelancing',
        'internship' => 'Internship',
        'student' => 'Student',
    ];

    public const EDUCATION_STATUS_LABELS = [
        'studying' => 'Studying',
        'graduated' => 'Graduated',
        'paused' => 'Paused',
        'not_studying' => 'Not Studying',
    ];

    protected $fillable = [
        'student_code', 'photo', 'name_khmer', 'name_english', 'name_chinese', 'gender',
        'date_of_birth', 'nationality', 'national_id', 'marital_status', 'phone', 'telegram',
        'email', 'facebook_url', 'linkedin_url', 'address', 'province', 'country',
        'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone',
        'preferred_channel', 'preferred_language', 'registered_date', 'status', 'student_stage', 'graduation_status',
        'education_status', 'employment_status', 'is_currently_studying', 'is_currently_employed',
        'is_seeking_employment', 'professional_skills', 'technical_skills', 'certifications',
        'portfolio_url', 'cv_path', 'career_goals', 'interested_jobs', 'interested_internships',
        'interested_mentoring', 'interested_trainer', 'interested_volunteering', 'career_notes',
        'alumni_membership_number', 'alumni_group', 'interested_activities', 'interested_newsletter',
        'interested_job_announcements', 'interested_training', 'last_contacted_date',
        'next_followup_date', 'engagement_status', 'pipeline_stage', 'referral_info', 'alumni_feedback',
        'engagement_notes', 'assigned_staff_id', 'campus_id', 'created_by', 'updated_by', 'record_status',
        'internal_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'campus_id' => 'integer',
        'registered_date' => 'date',
        'last_contacted_date' => 'date',
        'next_followup_date' => 'date',
        'is_currently_studying' => 'boolean',
        'is_currently_employed' => 'boolean',
        'is_seeking_employment' => 'boolean',
        'interested_jobs' => 'boolean',
        'interested_internships' => 'boolean',
        'interested_mentoring' => 'boolean',
        'interested_trainer' => 'boolean',
        'interested_volunteering' => 'boolean',
        'interested_activities' => 'boolean',
        'interested_newsletter' => 'boolean',
        'interested_job_announcements' => 'boolean',
        'interested_training' => 'boolean',
    ];

    public static function generateStudentCode(): string
    {
        $year = Carbon::now()->format('Y');
        // Scoped out on purpose: student_code is unique across the whole centre, so the
        // sequence must not restart at a campus the signed-in account is filtered to.
        $last = static::withoutGlobalScopes()
            ->where('student_code', 'like', "STU-$year-%")
            ->orderByDesc('student_code')
            ->value('student_code');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return sprintf('STU-%s-%04d', $year, $seq);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function studentCourses(): HasMany
    {
        return $this->hasMany(StudentCourse::class);
    }

    public function faceProfile(): HasOne
    {
        return $this->hasOne(StudentFaceProfile::class);
    }

    public function schoolClubs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SchoolClub::class, 'school_club_student');
    }

    public function studyGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(StudyGroup::class, 'study_group_student')
            ->withTimestamps();
    }

    public function educationHistories(): HasMany
    {
        return $this->hasMany(EducationHistory::class);
    }

    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function followUpTasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }


    public function notes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function opportunityApplications(): HasMany
    {
        return $this->hasMany(OpportunityApplication::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StudentMovement::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function currentEmployment()
    {
        return $this->employmentHistories()->where('is_current', true)->first();
    }

    public function currentEducation()
    {
        return $this->educationHistories()->where('is_current', true)->first();
    }

    public function activeEnrollment()
    {
        return $this->studentCourses()
            ->whereIn('enrollment_status', ['in_progress', 'deferred'])
            ->orderByDesc('id')
            ->first();
    }

    public function latestStudentCourse()
    {
        return $this->studentCourses()->orderByDesc('graduation_year')->orderByDesc('id')->first();
    }

    public function graduationYear(): ?int
    {
        $sc = $this->relationLoaded('studentCourses')
            ? $this->studentCourses->sortByDesc('graduation_year')->first()
            : $this->latestStudentCourse();
        return $sc?->graduation_year;
    }

    public function courseName(): ?string
    {
        $sc = $this->relationLoaded('studentCourses')
            ? $this->studentCourses->sortByDesc('graduation_year')->first()
            : $this->latestStudentCourse();
        return $sc?->course?->name;
    }

    public function canBeContacted(): bool
    {
        return $this->status !== 'do_not_contact';
    }

    /** Percentage of key profile fields completed (0-100). */
    public function completionPercentage(): int
    {
        $keyFields = [
            'name_khmer', 'name_english', 'gender', 'date_of_birth', 'nationality', 'phone',
            'email', 'telegram', 'address', 'province', 'preferred_channel', 'preferred_language',
            'emergency_contact_name', 'emergency_contact_phone', 'education_status',
            'employment_status', 'career_goals', 'alumni_membership_number', 'engagement_status',
        ];
        $filled = 0;
        foreach ($keyFields as $f) {
            if (!empty($this->{$f})) {
                $filled++;
            }
        }
        if ($this->studentCourses()->exists()) {
            $filled += 2;
        }
        return (int) round($filled / (count($keyFields) + 2) * 100);
    }
}
