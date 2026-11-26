@extends('layouts.app')

@section('title', 'Student Change Approvals')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-clipboard-check text-primary me-1"></i>Student Change Approvals</h1>
        <div class="page-subtitle">Review staff-submitted student profile edits before they are applied. New course enrollments are added immediately and do not appear here.</div>
    </div>
    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people me-1"></i>Students</a>
</div>

<ul class="nav nav-tabs mb-3" role="tablist" aria-label="Student change request status">
    @foreach(['all' => 'All requests', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $tabStatus => $label)
        <li class="nav-item" role="presentation">
            <a href="{{ route('student-change-requests.index', ['status' => $tabStatus]) }}"
                class="nav-link {{ $status === $tabStatus ? 'active' : '' }}"
                @if($status === $tabStatus) aria-current="page" @endif>
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Requested by</th>
                    <th>Submitted</th>
                    <th>Changed values (old → new)</th>
                    <th>Status</th>
                    <th>Reviewed by</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
        @forelse($requests as $changeRequest)
            @php
                $payload = $changeRequest->payload ?? [];
                $stagedFiles = $payload['_staged_files'] ?? [];
                $original = $payload['_original'] ?? [];
                $hasStoredDiff = array_key_exists('_changes', $payload);
                $changes = $payload['_changes'] ?? [];
                unset($payload['_staged_files']);
                unset($payload['_original']);
                unset($payload['_changes']);
                $formatValue = function ($value): string {
                    if (is_array($value)) {
                        return collect($value)->map(fn ($item) => is_bool($item) ? ($item ? 'Yes' : 'No') : $item)->join(', ') ?: '—';
                    }
                    if (is_bool($value)) {
                        return $value ? 'Yes' : 'No';
                    }
                    return filled($value) ? (string) $value : '—';
                };
                $labels = [
                    'name_english' => 'English name', 'name_khmer' => 'Khmer name', 'name_chinese' => 'Chinese name',
                    'gender' => 'Gender', 'date_of_birth' => 'Date of birth', 'nationality' => 'Nationality',
                    'national_id' => 'National ID / Passport', 'marital_status' => 'Marital status',
                    'phone' => 'Phone', 'telegram' => 'Telegram', 'email' => 'Email',
                    'facebook_url' => 'Facebook URL', 'linkedin_url' => 'LinkedIn URL', 'address' => 'Address',
                    'province' => 'Province / city', 'country' => 'Country',
                    'emergency_contact_name' => 'Emergency contact name',
                    'emergency_contact_relationship' => 'Emergency contact relationship',
                    'emergency_contact_phone' => 'Emergency contact phone', 'preferred_channel' => 'Preferred channel',
                    'preferred_language' => 'Preferred language', 'registered_date' => 'Registered date',
                    'status' => 'Student status', 'student_stage' => 'Lifecycle stage',
                    'graduation_status' => 'Graduation status', 'education_status' => 'Education status',
                    'employment_status' => 'Employment status', 'is_currently_studying' => 'Currently studying',
                    'is_currently_employed' => 'Currently employed', 'is_seeking_employment' => 'Seeking employment',
                    'professional_skills' => 'Professional skills', 'technical_skills' => 'Technical skills',
                    'certifications' => 'Certifications', 'portfolio_url' => 'Portfolio URL',
                    'career_goals' => 'Career goals', 'career_notes' => 'Career notes',
                    'interested_jobs' => 'Interested in jobs', 'interested_internships' => 'Interested in internships',
                    'interested_mentoring' => 'Interested in mentoring', 'interested_trainer' => 'Interested in training',
                    'interested_volunteering' => 'Interested in volunteering', 'interested_activities' => 'Interested in activities',
                    'interested_newsletter' => 'Newsletter consent', 'interested_job_announcements' => 'Job announcement consent',
                    'interested_training' => 'Interested in training', 'alumni_membership_number' => 'Alumni membership number',
                    'alumni_group' => 'Alumni group', 'engagement_status' => 'Engagement status',
                    'last_contacted_date' => 'Last contacted date', 'next_followup_date' => 'Next follow-up date',
                    'referral_info' => 'Referral information', 'alumni_feedback' => 'Alumni feedback',
                    'engagement_notes' => 'Engagement notes', 'assigned_staff_id' => 'Assigned staff ID',
                    'internal_notes' => 'Internal notes', 'school_club_ids' => 'School clubs',
                    'study_group_ids' => 'Study groups',
                    'sc_campus_id' => 'Center education campus ID', 'sc_course_id' => 'Center education course ID',
                    'sc_academic_year' => 'Center education academic year ID', 'sc_intake_id' => 'Center education intake ID',
                    'sc_program_id' => 'Center education program ID', 'sc_semester_id' => 'Center education semester ID',
                    'sc_batch_id' => 'Center education batch ID', 'sc_term' => 'Center education term',
                    'sc_registration_number' => 'Center education registration number', 'sc_major' => 'Center education major',
                    'sc_class_group' => 'Center education class / group', 'sc_instructor_name' => 'Center education instructor',
                    'sc_enrollment_date' => 'Center education enrollment date', 'sc_course_start_date' => 'Center education start date',
                    'sc_course_completion_date' => 'Center education completion date', 'sc_graduation_date' => 'Center education graduation date',
                    'sc_graduation_year' => 'Center education graduation year', 'sc_attendance_percentage' => 'Center education attendance',
                    'sc_grade' => 'Center education grade', 'sc_certificate_received' => 'Certificate received',
                    'sc_certificate_number' => 'Certificate number', 'sc_scholarship_status' => 'Scholarship status',
                    'sc_internship_participation' => 'Internship participation', 'sc_internship_company' => 'Internship company',
                    'sc_graduation_project' => 'Graduation project', 'sc_withdrawal_reason' => 'Withdrawal reason',
                    'sc_alumni_membership_status' => 'Course alumni membership status',
                    'sc_alumni_membership_date' => 'Course alumni membership date', 'sc_notes' => 'Center education notes',
                    'edu_institution' => 'Further education institution', 'edu_degree_program' => 'Further education degree / program',
                    'edu_major' => 'Further education major', 'edu_year_level' => 'Further education year level',
                    'edu_start_date' => 'Further education start date', 'edu_expected_graduation_date' => 'Expected graduation date',
                    'edu_scholarship_info' => 'Further education scholarship', 'edu_further_plans' => 'Further education plans',
                    'emp_company' => 'Employer', 'emp_job_title' => 'Job title', 'emp_industry' => 'Industry',
                    'emp_department' => 'Department', 'emp_company_address' => 'Company address',
                    'emp_work_phone' => 'Work phone', 'emp_work_email' => 'Work email', 'emp_start_date' => 'Employment start date',
                    'emp_salary_range' => 'Salary range', 'emp_employment_type' => 'Employment type',
                ];
                if (!$hasStoredDiff) {
                    foreach ($payload as $key => $value) {
                        if (!str_starts_with($key, '_') && $formatValue($original[$key] ?? null) !== $formatValue($value)) {
                            $changes[$key] = ['old' => $original[$key] ?? null, 'new' => $value];
                        }
                    }
                }
            @endphp
            <tr>
                <td>
                    <a class="fw-semibold text-decoration-none" href="{{ route('students.show', $changeRequest->student_id) }}">
                        {{ $changeRequest->student?->name_english ?? 'Student record unavailable' }}
                    </a>
                    <div class="small text-muted">{{ $changeRequest->student?->student_code ?? '—' }}</div>
                </td>
                <td>{{ $changeRequest->requester?->name ?? 'Former user' }}</td>
                <td class="text-nowrap">{{ $changeRequest->created_at->format('M d, Y') }}<div class="small text-muted">{{ $changeRequest->created_at->format('g:i A') }}</div></td>
                <td style="min-width:180px;max-width:280px">
                    @if($changes || $stagedFiles)
                        <div class="small text-muted mb-1">{{ count($changes) + count($stagedFiles) }} changed item(s)</div>
                    @endif
                    @forelse($changes as $key => $change)
                        <div class="small text-wrap">
                            <strong>{{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</strong>
                            {{ $formatValue($change['old'] ?? null) }}
                            <i class="bi bi-arrow-right mx-1"></i>
                            <strong>{{ $formatValue($change['new'] ?? null) }}</strong>
                        </div>
                    @empty
                        @if(!$stagedFiles)
                            <span class="small text-muted">No field changes detected</span>
                        @endif
                    @endforelse
                    @foreach($stagedFiles as $kind => $file)
                        <div class="small text-wrap"><strong>{{ ucfirst($kind) }}:</strong> existing file <i class="bi bi-arrow-right mx-1"></i><strong>{{ $file['original_name'] ?? 'Uploaded file' }}</strong></div>
                    @endforeach
                    @if($changeRequest->request_remarks)
                        <div class="small text-muted mt-2"><strong>Submitter note:</strong> {{ $changeRequest->request_remarks }}</div>
                    @endif
                    @if($changeRequest->review_note)
                        <div class="small text-muted mt-2"><strong>Review note:</strong> {{ $changeRequest->review_note }}</div>
                    @endif
                </td>
                <td><span class="badge text-bg-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$changeRequest->status] ?? 'secondary' }}">{{ ucfirst($changeRequest->status) }}</span></td>
                <td>
                    {{ $changeRequest->reviewer?->name ?? '—' }}
                    @if($changeRequest->reviewed_at)<div class="small text-muted">{{ $changeRequest->reviewed_at->format('M d, Y') }}</div>@endif
                </td>
                <td class="text-end text-nowrap">
                    @if($changeRequest->status === 'pending')
                        <form method="POST" action="{{ route('student-change-requests.review', $changeRequest) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="decision" value="approve">
                            <button class="btn btn-sm btn-success" title="Approve and apply" data-confirm="Apply these changes to the student record?"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <form method="POST" action="{{ route('student-change-requests.review', $changeRequest) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="decision" value="reject">
                            <button class="btn btn-sm btn-outline-danger" title="Reject" data-confirm="Reject this request? No student information will be changed."><i class="bi bi-x-lg"></i></button>
                        </form>
                    @else
                        <span class="small text-muted">Reviewed</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="empty-state py-5"><i class="bi bi-clipboard-check"></i><p class="mb-1">No {{ $status === 'all' ? '' : $status . ' ' }}change requests.</p></div></td></tr>
        @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="card-footer bg-transparent">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
