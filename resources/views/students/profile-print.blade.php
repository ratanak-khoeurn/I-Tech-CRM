<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile — {{ $student->name_english }} ({{ $student->student_code }})</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; background: #fff; }
        .section-title { background: #1565c0; color: #fff; padding: 4px 10px; font-weight: 700; font-size: 13px; border-radius: 4px; margin: 14px 0 8px; }
        table.info { width: 100%; }
        table.info td { padding: 3px 6px; border-bottom: 1px solid #eee; vertical-align: top; }
        table.info td.lbl { width: 28%; color: #555; font-weight: 600; }
        .no-print { position: sticky; top: 0; z-index: 10; }
        @media print { .no-print { display: none !important; } body { font-size: 11px; } }
    </style>
</head>
<body>
@php
    $sc = $student->studentCourses->sortByDesc('graduation_year')->first();
    $curEdu = $student->educationHistories->firstWhere('is_current', true);
@endphp
@unless(request()->boolean('preview'))
    <div class="no-print bg-light border-bottom p-2 mb-3 d-flex justify-content-between">
        <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-secondary">&larr; Back to Profile</a>
        <button onclick="window.print()" class="btn btn-sm btn-primary"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    </div>
@endunless
<div class="container" style="max-width: 900px;">
    <div class="text-center mb-3">
        <h4 class="mb-0" style="color:#1565c0">{{ \App\Models\Setting::get('center_name', 'Education Center') }}</h4>
        <div class="text-muted">Student / Alumni Profile — Exported {{ now()->format('M d, Y H:i') }} by {{ auth()->user()->name }}</div>
    </div>

    <h5 class="border-bottom pb-1">{{ $student->name_english }} <small class="text-muted">({{ $student->student_code }})</small></h5>

    <div class="section-title">A. Personal Information</div>
    <table class="info">
        <tr><td class="lbl">Khmer Name</td><td>{{ $student->name_khmer ?: '—' }}</td><td class="lbl">Chinese Name</td><td>{{ $student->name_chinese ?: '—' }}</td></tr>
        <tr><td class="lbl">Gender</td><td>{{ ucfirst((string) $student->gender) }}</td><td class="lbl">Date of Birth</td><td>{{ $student->date_of_birth?->format('M d, Y') ?: '—' }}</td></tr>
        <tr><td class="lbl">Nationality</td><td>{{ $student->nationality ?: '—' }}</td><td class="lbl">Marital Status</td><td>{{ ucfirst((string) $student->marital_status) }}</td></tr>
        <tr><td class="lbl">Phone</td><td>{{ $student->phone }}</td><td class="lbl">Telegram</td><td>{{ $student->telegram ?: '—' }}</td></tr>
        <tr><td class="lbl">Email</td><td>{{ $student->email ?: '—' }}</td><td class="lbl">Facebook</td><td>{{ $student->facebook_url ?: '—' }}</td></tr>
        <tr><td class="lbl">Address</td><td>{{ trim(($student->address ?? '') . ', ' . ($student->province ?? '') . ', ' . ($student->country ?? ''), ', ') ?: '—' }}</td><td class="lbl">LinkedIn</td><td>{{ $student->linkedin_url ?: '—' }}</td></tr>
        <tr><td class="lbl">Emergency Contact</td><td>{{ $student->emergency_contact_name ?: '—' }} {{ $student->emergency_contact_relationship ? '(' . $student->emergency_contact_relationship . ')' : '' }} {{ $student->emergency_contact_phone }}</td>
            <td class="lbl">Preferred Channel</td><td>{{ ucfirst(str_replace('_', ' ', (string) $student->preferred_channel)) }} / {{ $student->preferred_language ?: '—' }}</td></tr>
        <tr><td class="lbl">Contact Status</td><td>{{ \App\Models\Student::STATUS_LABELS[$student->status] }}</td><td class="lbl">Lifecycle Stage</td><td>{{ \App\Models\Student::STAGE_LABELS[$student->student_stage] ?? $student->student_stage }}</td></tr>
        <tr><td class="lbl">Graduation Status</td><td>{{ \App\Models\Student::GRADUATION_STATUS_LABELS[$student->graduation_status] ?? 'Graduation status not set' }}</td><td class="lbl">Registered</td><td>{{ $student->registered_date?->format('M d, Y') }}</td></tr>
    </table>

    <div class="section-title">B. Education Center Information</div>
    @if($sc)
    <table class="info">
        <tr><td class="lbl">Campus</td><td>{{ $sc->campuses?->name ?: '—' }}</td><td class="lbl">Program</td><td>{{ $sc->courses?->program->name ?: '—' }}</td></tr>
        <tr><td class="lbl">Course Category</td><td>{{ $sc->course?->category?->name_en ?: $sc->course?->course_category ?: '—' }}</td><td class="lbl">Course</td><td>{{ $sc->course?->name ?: '—' }}</td></tr>
        <tr><td class="lbl">Academic Year</td><td>{{ $sc->semester?->academicYear?->academic_year ?? $sc->intake?->academic_year ?? '—' }}</td><td class="lbl">Semester / Term</td><td>{{ $sc->semester?->name ?? '—' }}{{ $sc->semester?->term ? ' / Term ' . $sc->semester->term : '' }}</td></tr>
        <tr><td class="lbl">Batch</td><td>{{ $sc->batch?->name ?: '—' }}</td><td class="lbl">Study Time</td><td>{{ $sc->studyGroup?->study_time ?: $sc->batch?->study_time ?: '—' }}</td></tr>
        <tr><td class="lbl">Study Group</td><td>{{ $sc->studyGroup?->name ?: '—' }}</td><td class="lbl">Group Code</td><td>{{ $sc->studyGroup?->code ?: '—' }}</td></tr>
        <tr><td class="lbl">Registration No.</td><td>{{ $sc->registration_number ?: '—' }}</td><td class="lbl">Class / Group</td><td>{{ $sc->class_group ?: '—' }}</td></tr>
        <tr><td class="lbl">Instructor</td><td>{{ $sc->instructor_name ?: '—' }}</td><td class="lbl">Graduation Year</td><td>{{ $sc->graduation_year ?: '—' }}</td></tr>
        <tr><td class="lbl">Enrolled</td><td>{{ $sc->enrollment_date?->format('M d, Y') ?: '—' }}</td><td class="lbl">Graduated</td><td>{{ $sc->graduation_date?->format('M d, Y') ?: '—' }}</td></tr>
        <tr><td class="lbl">Attendance</td><td>{{ $sc->attendance_percentage !== null ? $sc->attendance_percentage . '%' : '—' }}</td><td class="lbl">Grade</td><td>{{ $sc->grade ?: '—' }}</td></tr>
        <tr><td class="lbl">Certificate</td><td>{{ $sc->certificate_received ? 'Received' . ($sc->certificate_number ? ' #' . $sc->certificate_number : '') : 'Not received' }}</td><td class="lbl">Scholarship</td><td>{{ $sc->scholarship_status ?: '—' }}</td></tr>
        <tr><td class="lbl">Internship</td><td>{{ $sc->internship_participation ? 'Yes' . ($sc->internship_company ? ' @ ' . $sc->internship_company : '') : 'No' }}</td><td class="lbl">Project</td><td>{{ $sc->graduation_project ?: '—' }}</td></tr>
    </table>
    @else
        <p class="text-muted">No center education record.</p>
    @endif

    <div class="section-title">C. Current Education</div>
    @if($curEdu)
    <table class="info">
        <tr><td class="lbl">Institution</td><td>{{ $curEdu->institution ?: '—' }}</td><td class="lbl">Degree / Program</td><td>{{ $curEdu->degree_program ?: '—' }}</td></tr>
        <tr><td class="lbl">Major</td><td>{{ $curEdu->major ?: '—' }}</td><td class="lbl">Year / Level</td><td>{{ $curEdu->year_level ?: '—' }}</td></tr>
        <tr><td class="lbl">Start</td><td>{{ $curEdu->start_date?->format('M Y') ?: '—' }}</td><td class="lbl">Expected Graduation</td><td>{{ $curEdu->expected_graduation_date?->format('M Y') ?: '—' }}</td></tr>
        <tr><td class="lbl">Status</td><td>{{ ucfirst((string) $curEdu->status) }}</td><td class="lbl">Scholarship</td><td>{{ $curEdu->scholarship_info ?: '—' }}</td></tr>
    </table>
    @else
        <p class="text-muted">Not currently studying.</p>
    @endif

    <div class="section-title">D. Career Information</div>
    <table class="info">
        <tr><td class="lbl">Employment Status</td><td>{{ \App\Models\Student::EMPLOYMENT_STATUS_LABELS[$student->employment_status] ?? '—' }}</td><td class="lbl">Professional Skills</td><td>{{ $student->professional_skills ?: '—' }}</td></tr>
        <tr><td class="lbl">Technical Skills</td><td>{{ $student->technical_skills ?: '—' }}</td><td class="lbl">Career Goals</td><td>{{ $student->career_goals ?: '—' }}</td></tr>
    </table>
    @foreach($student->employmentHistories->sortByDesc('is_current') as $e)
        <div class="mb-1">
            <strong>{{ $e->job_title }} @ {{ $e->company }}</strong>
            {{ $e->is_current ? '(Current)' : '(' . ($e->start_date?->format('M Y') ?? '?') . ' – ' . ($e->end_date?->format('M Y') ?? '?') . ')' }}
            <span class="text-muted">— {{ $e->industry }}</span>
        </div>
    @endforeach

    <div class="section-title">F. Alumni Engagement</div>
    <table class="info">
        <tr><td class="lbl">Membership No.</td><td>{{ $student->alumni_membership_number ?: '—' }}</td><td class="lbl">Alumni Group</td><td>{{ $student->alumni_group ?: '—' }}</td></tr>
        <tr><td class="lbl">Engagement Status</td><td>{{ ucfirst(str_replace('_', ' ', (string) $student->engagement_status)) }}</td><td class="lbl">Last Contacted</td><td>{{ $student->last_contacted_date?->format('M d, Y') ?: 'Never' }}</td></tr>
        <tr><td class="lbl">Consents</td><td colspan="3">
            Activities: {{ $student->interested_activities ? 'Yes' : 'No' }} ·
            Newsletter: {{ $student->interested_newsletter ? 'Yes' : 'No' }} ·
            Job announcements: {{ $student->interested_job_announcements ? 'Yes' : 'No' }} ·
            Training: {{ $student->interested_training ? 'Yes' : 'No' }}
        </td></tr>
        @if($student->alumni_feedback)
        <tr><td class="lbl">Feedback</td><td colspan="3">{{ $student->alumni_feedback }}</td></tr>
        @endif
    </table>

    <div class="section-title">Communication History ({{ $student->communicationLogs->count() }})</div>
    <table class="table table-sm table-bordered">
        <thead><tr><th>Date</th><th>Channel</th><th>Dir.</th><th>Subject</th><th>Outcome</th><th>By</th></tr></thead>
        <tbody>
        @forelse($student->communicationLogs->sortByDesc('contact_datetime') as $log)
            <tr>
                <td>{{ $log->contact_datetime->format('Y-m-d H:i') }}</td>
                <td>{{ \App\Models\CommunicationLog::CHANNELS[$log->channel] ?? $log->channel }}</td>
                <td>{{ ucfirst($log->direction) }}</td>
                <td>{{ $log->subject }}</td>
                <td>{{ $log->outcome }}</td>
                <td>{{ $log->user?->name }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">No communications.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="section-title">Follow-up Tasks ({{ $student->followUpTasks->count() }})</div>
    <table class="table table-sm table-bordered">
        <thead><tr><th>Title</th><th>Due</th><th>Priority</th><th>Status</th><th>Result</th></tr></thead>
        <tbody>
        @forelse($student->followUpTasks->sortByDesc('due_date') as $t)
            <tr><td>{{ $t->title }}</td><td>{{ $t->due_date->format('Y-m-d') }}</td><td>{{ ucfirst($t->priority) }}</td><td>{{ ucfirst(str_replace('_', ' ', $t->status)) }}</td><td>{{ $t->result }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-muted">No tasks.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="section-title">Events & Activities ({{ $student->eventRegistrations->count() }})</div>
    <table class="table table-sm table-bordered">
        <thead><tr><th>Event</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($student->eventRegistrations as $r)
            <tr><td>{{ $r->event?->title }}</td><td>{{ $r->event?->start_datetime?->format('Y-m-d') }}</td><td>{{ ucfirst(str_replace('_', ' ', $r->status)) }}</td></tr>
        @empty
            <tr><td colspan="3" class="text-muted">No event participation.</td></tr>
        @endforelse
        </tbody>
    </table>

    <p class="text-muted mt-4 mb-2" style="font-size:10px">
        Confidential — contains personal data. Handle per the center's privacy policy.
        Assigned staff: {{ $student->assignedStaff?->name ?? 'Unassigned' }}.
    </p>
</div>
</body>
</html>
