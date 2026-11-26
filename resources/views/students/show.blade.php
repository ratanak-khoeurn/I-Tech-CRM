@extends('layouts.app')

@section('title', $student->name_english)

@php
    $sc = $student->studentCourses->sortByDesc('graduation_year')->first();
    $activeEnrollment = $student->studentCourses
        ->filter(fn ($e) => $e->isCurrent())
        ->sortBy(fn ($e) => [$e->enrollment_status === 'in_progress' ? 0 : 1, -$e->id])
        ->first()
        ?? $student->studentCourses->sortByDesc('id')->first();
    $currentCourseInvoice = null;
    if ($activeEnrollment) {
        $courseInvoices = $student->invoices->filter(fn ($invoice) => (int) $invoice->student_course_id === (int) $activeEnrollment->id);
        $currentCourseInvoice = $courseInvoices->sortByDesc('id')->first();

        if (!$currentCourseInvoice && $activeEnrollment->course_id) {
            $currentCourseInvoice = $student->invoices
                ->filter(fn ($invoice) => !$invoice->student_course_id
                    && (int) $invoice->feeStructure?->course_id === (int) $activeEnrollment->course_id)
                ->sortByDesc('id')
                ->first();
        }
    }
    $completedCount = $student->studentCourses->where('enrollment_status', 'completed')->count();
    $faceProfile = $student->faceProfile;
    $curEdu = $student->educationHistories->firstWhere('is_current', true);
    $curEmp = $student->employmentHistories->firstWhere('is_current', true);
    $completion = $student->completionPercentage();
    $statusColors = ['active_alumni' => 'success', 'inactive' => 'warning', 'unreachable' => 'secondary', 'do_not_contact' => 'danger'];
@endphp

@push('styles')
<style>
    #enrollmentModal .modal-dialog {
        width: calc(100% - 1rem);
        max-width: 1140px;
        margin: .5rem auto;
    }
    #enrollmentModal .modal-content { width: 100%; }
    @media (max-width: 575.98px) {
        #enrollmentModal .modal-dialog {
            width: 100%;
            margin: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
            <li class="breadcrumb-item active">{{ $student->name_english }}</li>
        </ol>
        <h1 class="page-title">{{ $student->name_english }}</h1>
    </nav>
    <div class="d-flex gap-2 flex-wrap">

        @if($student->graduation_status === 'graduated')
            <a href="{{ route('students.certificate', $student) }}" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-award me-1"></i>Print Certificate</a>
        @endif
        @if(auth()->user()->canManageStudents() && (auth()->user()->isAdmin() || $student->assigned_staff_id === auth()->id()))
            <a href="{{ route('students.edit', $student) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit Profile</a>
            @if($student->record_status === 'active')
                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#faceEnrollmentModal">
                    <i class="bi bi-person-bounding-box me-1"></i>{{ $faceProfile?->is_active ? 'Update face profile' : 'Enroll face' }}
                </button>
            @endif
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#interactionModal"><i class="bi bi-chat-dots me-1"></i>Add Interaction</button>
            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#taskModal"><i class="bi bi-bell me-1"></i>Schedule Follow-up</button>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal"><i class="bi bi-upload me-1"></i>Upload Document</button>
        @endif
        <a href="{{ route('students.profile-export', $student) }}" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-printer me-1"></i>Export Profile</a>
        <a href="{{ route('students.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Back</a>
    </div>
</div>

@if($student->status === 'do_not_contact')
    <div class="alert alert-danger py-2"><i class="bi bi-slash-circle me-1"></i><strong>Do Not Contact:</strong> this student has opted out of outreach. Do not log outgoing communications.</div>
@endif
@if($student->record_status === 'archived')
    <div class="alert alert-secondary py-2"><i class="bi bi-archive me-1"></i>This record is archived.</div>
@endif
@if($pendingChangeRequest)
    <div class="alert alert-warning py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-hourglass-split me-1"></i>Profile changes submitted by {{ $pendingChangeRequest->requester?->name ?? 'staff' }} are awaiting administrator approval. The current profile remains unchanged until approved.</span>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('student-change-requests.index') }}" class="btn btn-sm btn-outline-dark">Review request</a>
        @endif
    </div>
@endif

<!-- Header card -->
<div class="card profile-header-card mb-3">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
        @if($student->photo)
            <img src="{{ asset('storage/' . $student->photo) }}" class="profile-avatar" alt="{{ $student->name_english }}">
        @else
            <span class="profile-avatar-placeholder">{{ strtoupper(substr($student->name_english, 0, 1)) }}</span>
        @endif
        <div class="flex-grow-1">
            <h4 class="mb-1">{{ $student->name_english }}
                <span class="badge bg-{{ $statusColors[$student->status] ?? 'secondary' }} align-middle ms-1">{{ \App\Models\Student::STATUS_LABELS[$student->status] }}</span>
                <span class="badge bg-primary align-middle ms-1">{{ \App\Models\Student::STAGE_LABELS[$student->student_stage] ?? $student->student_stage }}</span>
                <span class="badge bg-{{ $student->graduation_status === 'graduated' ? 'success' : 'secondary' }} align-middle ms-1">{{ \App\Models\Student::GRADUATION_STATUS_LABELS[$student->graduation_status] ?? 'Graduation status not set' }}</span>
            </h4>
            <div class="small opacity-75">
                <i class="bi bi-hash"></i>{{ $student->student_code }}
                @if($student->name_khmer) · {{ $student->name_khmer }} @endif
                @if($activeEnrollment?->course)
                    · {{ $activeEnrollment->course->name }} ({{ $activeEnrollment->statusLabel }})
                    @if($currentCourseInvoice)
                        @php
                            $headerPaymentStatus = match (true) {
                                $currentCourseInvoice->status === 'cancelled' => ['Invoice voided', 'secondary'],
                                $currentCourseInvoice->total() > 0 && $currentCourseInvoice->paidAmount() >= $currentCourseInvoice->total() => ['Paid', 'success'],
                                $currentCourseInvoice->paidAmount() > 0 => ['Partially paid · $' . number_format(max(0, $currentCourseInvoice->balance()), 2) . ' due', 'warning'],
                                $currentCourseInvoice->status === 'overdue' => ['Not paid · overdue', 'danger'],
                                default => ['Not paid', 'danger'],
                            };
                        @endphp
                        <span class="badge text-bg-{{ $headerPaymentStatus[1] }} ms-1">Payment: {{ $headerPaymentStatus[0] }}</span>
                    @else
                        <span class="badge text-bg-secondary ms-1">Payment: No invoice</span>
                    @endif
                @endif
                @if($completedCount) · {{ $completedCount }} course{{ $completedCount > 1 ? 's' : '' }} completed @endif
                @if($sc?->graduation_year) · Class of {{ $sc->graduation_year }} @endif
            </div>
            <div class="small mt-1">
                <span class="me-3"><i class="bi bi-telephone me-1"></i>{{ $student->phone }}</span>
                @if($student->email)<span class="me-3"><i class="bi bi-envelope me-1"></i>{{ $student->email }}</span>@endif
                @if($student->telegram)<span class="me-3"><i class="bi bi-telegram me-1"></i>{{ $student->telegram }}</span>@endif
                @if($student->province)<span><i class="bi bi-geo-alt me-1"></i>{{ $student->province }}</span>@endif
            </div>
        </div>
        <div class="text-end" style="min-width:180px">
            <div class="small opacity-75 mb-1">Profile Completion</div>
            <div class="progress completion-bar bg-white bg-opacity-25 mb-1">
                <div class="progress-bar bg-white" style="width: {{ $completion }}%"></div>
            </div>
            <div class="fw-bold">{{ $completion }}%</div>
            <div class="small opacity-75">Assigned: {{ $student->assignedStaff?->name ?? 'Unassigned' }}</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto" role="tablist">
    @foreach(['overview' => ['speedometer2', 'Overview'], 'personal' => ['person', 'Personal'], 'education' => ['mortarboard', 'Education'],
              'career' => ['briefcase', 'Career'], 'communication' => ['chat-dots', 'Communication (' . $student->communicationLogs->count() . ')'],
              'tasks' => ['check2-square', 'Follow-up Tasks (' . $student->followUpTasks->count() . ')'],
              'events' => ['calendar-event', 'Events (' . $student->eventRegistrations->count() . ')'],
              'documents' => ['folder2-open', 'Documents (' . $student->documents->count() . ')'],
              'notes' => ['sticky', 'Notes (' . $student->notes->count() . ')']] as $key => [$icon, $label])
        <li class="nav-item">
            <button class="nav-link {{ $tab === $key ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $key }}" type="button">
                <i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content mb-4">
    <!-- Overview -->
    <div class="tab-pane fade {{ $tab === 'overview' ? 'show active' : '' }}" id="pane-overview">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100"><div class="card-header">Quick Facts</div><div class="card-body">
                    <div class="mb-2"><div class="info-label">Current Position</div><div class="info-value">{{ $curEmp ? $curEmp->job_title . ' @ ' . $curEmp->company : (\App\Models\Student::EMPLOYMENT_STATUS_LABELS[$student->employment_status] ?? '—') }}</div></div>
                    <div class="mb-2"><div class="info-label">Current Education</div><div class="info-value">{{ $curEdu ? $curEdu->degree_program . ', ' . $curEdu->institution : (\App\Models\Student::EDUCATION_STATUS_LABELS[$student->education_status] ?? '—') }}</div></div>
                    <div class="mb-2"><div class="info-label">Graduated From Center</div><div class="info-value">{{ $sc?->course?->name ?? '—' }} {{ $sc?->graduation_year ? '(' . $sc->graduation_year . ')' : '' }}</div></div>
                    <div class="mb-2">
                        <div class="info-label">Study Groups</div>
                        <div class="info-value">
                            @forelse($student->studyGroups as $studyGroup)
                                <a href="{{ route('study-groups.show', $studyGroup) }}" class="badge text-bg-light border text-decoration-none me-1 mb-1">
                                    {{ $studyGroup->name }}{{ $studyGroup->graduation_year ? ' · ' . $studyGroup->graduation_year : '' }}
                                </a>
                            @empty
                                <span class="text-muted fw-normal">—</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="mb-2"><div class="info-label">School Clubs</div><div class="info-value">{{ $student->schoolClubs->pluck('name')->join(', ') ?: '—' }}</div></div>
                    <div class="mb-2"><div class="info-label">Last Contacted</div><div class="info-value">{{ $student->last_contacted_date?->format('M d, Y') ?? 'Never' }}</div></div>
                    <div><div class="info-label">Next Follow-up</div><div class="info-value">{{ $student->next_followup_date?->format('M d, Y') ?? '—' }}</div></div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-header">Interests & Consent</div><div class="card-body">
                    @foreach(['interested_jobs' => 'Job opportunities', 'interested_internships' => 'Internships', 'interested_mentoring' => 'Mentoring students',
                              'interested_trainer' => 'Becoming a trainer', 'interested_volunteering' => 'Volunteering',
                              'interested_newsletter' => 'Newsletter (consent)', 'interested_job_announcements' => 'Job announcements (consent)'] as $f => $lbl)
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $lbl }}</span>
                            <span>{!! $student->{$f} ? '<i class="bi bi-check-circle-fill text-success"></i> Yes' : '<i class="bi bi-dash-circle text-muted"></i> No' !!}</span>
                        </div>
                    @endforeach
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-header">Recent Activity</div><div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($student->communicationLogs->sortByDesc('contact_datetime')->take(4) as $log)
                            <div class="list-group-item small">
                                <strong>{{ $log->subject }}</strong>
                                <div class="text-muted">{{ \App\Models\CommunicationLog::CHANNELS[$log->channel] ?? $log->channel }} · {{ $log->contact_datetime->format('M d, Y') }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-3 small"><i class="bi bi-chat-square" style="font-size:1.5rem"></i>No interactions yet</div>
                        @endforelse
                    </div>
                </div></div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-cash-coin text-primary me-1"></i>{{ $activeEnrollment?->isCurrent() ? 'Current Course' : 'Most Recent Course' }} Payment</span>
                @if($activeEnrollment)
                    <span class="small text-muted">{{ $activeEnrollment->course?->name ?? $activeEnrollment->center_name ?? 'Course enrollment' }}</span>
                @endif
            </div>
            <div class="card-body">
                @if(!$activeEnrollment)
                    <div class="text-muted small">No course enrollment is recorded for this student yet.</div>
                @elseif($currentCourseInvoice)
                    @php
                        $invoiceTotal = $currentCourseInvoice->total();
                        $invoicePaid = $currentCourseInvoice->paidAmount();
                        $paymentSummary = match (true) {
                            $currentCourseInvoice->status === 'cancelled' => ['Invoice voided', 'secondary'],
                            $invoiceTotal > 0 && $invoicePaid >= $invoiceTotal => ['Fully paid', 'success'],
                            $invoicePaid > 0 => ['Partially paid · $' . number_format(max(0, $currentCourseInvoice->balance()), 2) . ' due', 'warning'],
                            $currentCourseInvoice->status === 'overdue' => ['Overdue', 'danger'],
                            $currentCourseInvoice->status === 'draft' => ['Invoice pending', 'secondary'],
                            default => ['Not paid', 'danger'],
                        };
                    @endphp
                    <div class="row align-items-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="info-label">Invoice</div>
                            <div class="info-value">{{ $currentCourseInvoice->invoice_number }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-label">Total</div>
                            <div class="info-value">${{ number_format($invoiceTotal, 2) }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-label">Paid · Balance</div>
                            <div class="info-value text-nowrap">${{ number_format($invoicePaid, 2) }} · ${{ number_format(max(0, $currentCourseInvoice->balance()), 2) }}</div>
                        </div>
                        <div class="col-6 col-md-3 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                            <span class="badge text-bg-{{ $paymentSummary[1] }}">{{ $paymentSummary[0] }}</span>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('invoices.show', $currentCourseInvoice) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-receipt me-1"></i>Manage invoice
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="small text-muted mt-2">Recorded payments update this summary automatically. Payments must be entered on the invoice; they are not charged automatically.</div>
                @else
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="small text-muted"><i class="bi bi-info-circle me-1"></i>No invoice is linked to this course yet.</span>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('invoices.create', ['student_id' => $student->id, 'student_course_id' => $activeEnrollment->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-lg me-1"></i>Create course invoice
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Personal -->
    <div class="tab-pane fade {{ $tab === 'personal' ? 'show active' : '' }}" id="pane-personal">
        <div class="card"><div class="card-header">A. Basic Personal Information</div><div class="card-body">
            <div class="row g-3">
                @foreach([
                    'Student ID' => $student->student_code,
                    'Khmer Name' => $student->name_khmer, 'English Name' => $student->name_english,
                    'Chinese Name' => $student->name_chinese,
                    'Gender' => ucfirst((string) $student->gender),
                    'Date of Birth' => $student->date_of_birth?->format('M d, Y') . ($student->date_of_birth ? ' (' . $student->date_of_birth->age . ' yrs)' : ''),
                    'Nationality' => $student->nationality,
                    'National ID / Passport' => $canSensitive ? $student->national_id : ($student->national_id ? '•••••• (restricted)' : null),
                    'Marital Status' => ucfirst((string) $student->marital_status),
                    'Phone' => $student->phone, 'Telegram' => $student->telegram, 'Email' => $student->email,
                    'Facebook' => $student->facebook_url ? '<a href="' . e($student->facebook_url) . '" target="_blank" rel="noopener">' . e($student->facebook_url) . '</a>' : null,
                    'LinkedIn' => $student->linkedin_url ? '<a href="' . e($student->linkedin_url) . '" target="_blank" rel="noopener">' . e($student->linkedin_url) . '</a>' : null,
                    'Address' => $student->address, 'Province / City' => $student->province, 'Country' => $student->country,
                    'Emergency Contact' => trim(($student->emergency_contact_name ?? '') . ($student->emergency_contact_relationship ? ' (' . $student->emergency_contact_relationship . ')' : '')) ?: null,
                    'Emergency Phone' => $student->emergency_contact_phone,
                    'Preferred Channel' => ucfirst(str_replace('_', ' ', (string) $student->preferred_channel)),
                    'Preferred Language' => $student->preferred_language,
                    'Registered in CRM' => $student->registered_date?->format('M d, Y'),
                    'Status' => \App\Models\Student::STATUS_LABELS[$student->status] ?? $student->status,
                ] as $label => $value)
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value">{!! $value ?: '<span class="text-muted fw-normal">—</span>' !!}</div>
                    </div>
                @endforeach
            </div>
        </div></div>
    </div>

    <!-- Education -->
    <div class="tab-pane fade {{ $tab === 'education' ? 'show active' : '' }}" id="pane-education">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>B. Education Center Information</span>
                        @if(auth()->user()->canManageStudents() && (auth()->user()->isAdmin() || $student->assigned_staff_id === auth()->id()))
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#enrollmentModal" onclick="openEnrollmentModal(null, 'main')"><i class="bi bi-plus-lg me-1"></i>Add Course</button>
                                <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#enrollmentModal" onclick="openEnrollmentModal(null, 'supplementary')"><i class="bi bi-stars me-1"></i>Add Supplementary Course</button>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @php
                            $enrollments = $student->studentCourses
                                ->sortBy(fn ($enrollment) => [
                                    array_search($enrollment->enrollment_status, ['in_progress', 'deferred', 'completed', 'withdrawn'], true),
                                    -$enrollment->id,
                                ])
                                ->values();
                        @endphp
                        @if($enrollments->isEmpty())
                            <div class="empty-state py-3"><i class="bi bi-mortarboard"></i>No course enrollments yet. Use "Add Course" to enroll this student.</div>
                        @else
                            @foreach($enrollments as $i => $s)
                                @php
                                    $enrInvoice = $student->invoices
                                        ->filter(fn ($invoice) => (int) $invoice->student_course_id === (int) $s->id
                                            || (!$invoice->student_course_id && $s->course_id
                                                && (int) $invoice->feeStructure?->course_id === (int) $s->course_id))
                                        ->sortByDesc('id')
                                        ->first();
                                    $paymentRemark = !$enrInvoice
                                        ? ['No invoice', 'secondary']
                                        : match (true) {
                                            $enrInvoice->status === 'cancelled' => ['Invoice voided', 'secondary'],
                                            $enrInvoice->total() > 0 && $enrInvoice->paidAmount() >= $enrInvoice->total() => ['Paid', 'success'],
                                            $enrInvoice->paidAmount() > 0 => ['Partially paid · $' . number_format(max(0, $enrInvoice->balance()), 2) . ' due', 'warning'],
                                            $enrInvoice->status === 'overdue' => ['Not paid · overdue', 'danger'],
                                            $enrInvoice->status === 'draft' => ['Not paid · invoice pending', 'secondary'],
                                            default => ['Not paid', 'danger'],
                                        };
                                @endphp
                                <div class="border rounded p-2 mb-2 {{ $s->isCurrent() ? 'border-primary border-opacity-50' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                                        <div>
                                            <strong class="small">Course {{ $i + 1 }}: {{ $s->course?->name ?? ($s->center_name ?: 'Unknown course') }}</strong>
                                            <span class="badge {{ $s->statusBadgeClass }} ms-1" style="font-size:.65rem">{{ $s->statusLabel }}</span>
                                            <span class="badge text-bg-{{ $paymentRemark[1] }} ms-1" style="font-size:.65rem">Payment: {{ $paymentRemark[0] }}</span>
                                            @if($s->course?->isExtraCourse())
                                                <span class="badge text-bg-info ms-1" style="font-size:.65rem">Supplementary</span>
                                            @endif
                                        </div>
                                        @if(auth()->user()->canManageStudents() && (auth()->user()->isAdmin() || $student->assigned_staff_id === auth()->id()))
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:.72rem"
                                                    data-bs-toggle="modal" data-bs-target="#enrollmentModal"
                                                    onclick='openEnrollmentModal(@js($s->only(["id","course_id","campus_id","batch_id","intake_id","semester_id","registration_number","enrollment_status","enrollment_date","course_start_date","course_completion_date","graduation_date","grade","notes"]) + ["_extra" => (int) ($s->course?->isExtraCourse() ?? false)]))'>
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="POST" action="{{ route('students.enrollments.destroy', [$student, $s]) }}" onsubmit="return confirm('You are about to remove this **course enrollment**. Would you like to proceed?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size:.72rem"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row g-2">
                                        @foreach([
                                            'Center' => $s->center_name, 'Registration No.' => $s->registration_number,
                                            'Campus' => $s->campus?->name, 'Program' => $s->course?->program?->name,
                                            'Course Category' => $s->course?->category?->name_en ?: $s->course?->course_category,
                                            'Academic Year' => $s->semester?->academicYear?->academic_year ?? $s->intake?->academic_year,
                                            'Semester' => $s->semester?->name, 'Term' => $s->semester?->term,
                                            'Batch' => $s->batch?->name, 'Study Time' => $s->studyGroup?->study_time ?: $s->batch?->study_time,
                                            'Study Group' => $s->studyGroup?->name,
                                            'Major' => $s->major, 'Class / Group' => $s->class_group,
                                            'Instructor' => $s->instructor_name,
                                            'Enrollment Date' => $s->enrollment_date?->format('M d, Y'),
                                            'Start Date' => $s->course_start_date?->format('M d, Y'),
                                            'Completion' => $s->course_completion_date?->format('M d, Y'),
                                            'Graduation Date' => $s->graduation_date?->format('M d, Y'),
                                            'Graduation Year' => $s->graduation_year,
                                            'Attendance' => $s->attendance_percentage !== null ? $s->attendance_percentage . '%' : null,
                                            'Grade' => $s->grade,
                                            'Certificate' => $s->certificate_received ? 'Received' . ($s->certificate_number ? ' (#' . $s->certificate_number . ')' : '') : 'Not received',
                                            'Scholarship' => $s->scholarship_status,
                                            'Internship' => $s->internship_participation ? 'Yes' . ($s->internship_company ? ' @ ' . $s->internship_company : '') : 'No',
                                            'Graduation Project' => $s->graduation_project,
                                            'Withdrawal Reason' => $s->withdrawal_reason,
                                            'Alumni Membership' => trim(ucfirst(str_replace('_', ' ', (string) $s->alumni_membership_status)) . ($s->alumni_membership_date ? ' since ' . $s->alumni_membership_date->format('M Y') : '')) ?: null,
                                            'Notes' => $s->notes,
                                        ] as $label => $value)
                                            <div class="col-sm-6">
                                                <div class="info-label">{{ $label }}</div>
                                                <div class="info-value">{{ $value ?: '—' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($enrInvoice)
                                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1 flex-wrap gap-1">
                                            <span class="small text-muted">
                                                <i class="bi bi-receipt me-1"></i>{{ $enrInvoice->invoice_number }} ·
                                                Total {{ number_format($enrInvoice->total(), 2) }} ·
                                                Paid {{ number_format($enrInvoice->paidAmount(), 2) }} ·
                                                Balance {{ number_format($enrInvoice->balance(), 2) }}
                                            </span>
                                            <span class="badge text-bg-{{ $paymentRemark[1] }}">
                                                <i class="bi bi-{{ $enrInvoice->status === 'paid' ? 'check-circle' : ($enrInvoice->status === 'partial' ? 'clock' : 'exclamation-circle') }} me-1"></i>
                                                Payment: {{ $paymentRemark[0] }}
                                            </span>
                                        </div>
                                    @else
                                        @php
                                            $hasUnlinkedInvoice = $student->invoices->contains(fn ($invoice) => !$invoice->student_course_id && !$invoice->fee_structure_id);
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1 flex-wrap gap-1">
                                            <span class="small text-muted"><i class="bi bi-receipt me-1"></i>{{ $hasUnlinkedInvoice ? 'An invoice exists for this student but is not linked to this course.' : 'No invoice recorded for this course.' }}</span>
                                            <span class="badge text-bg-secondary">Payment: {{ $hasUnlinkedInvoice ? 'Not linked' : 'Not recorded' }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-header">C. Current & Further Education</div><div class="card-body">
                    @if($student->educationHistories->isEmpty())
                        <div class="empty-state py-3"><i class="bi bi-book"></i>No education history recorded</div>
                    @else
                        @foreach($student->educationHistories as $e)
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="small">{{ $e->institution }}</strong>
                                    @if($e->is_current)<span class="badge bg-primary" style="font-size:.65rem">CURRENT</span>@endif
                                </div>
                                <div class="small text-muted">{{ $e->degree_program }} {{ $e->major ? '— ' . $e->major : '' }} {{ $e->year_level ? '(' . $e->year_level . ')' : '' }}</div>
                                <div class="small">
                                    {{ $e->start_date?->format('M Y') }} → {{ $e->expected_graduation_date ? 'Expected ' . $e->expected_graduation_date->format('M Y') : '—' }}
                                    · Status: {{ ucfirst((string) $e->status) }}
                                </div>
                                @if($e->scholarship_info)<div class="small">Scholarship: {{ $e->scholarship_info }}</div>@endif
                                @if($e->further_education_plans)<div class="small">Plans: {{ $e->further_education_plans }}</div>@endif
                            </div>
                        @endforeach
                    @endif
                </div></div>
            </div>
        </div>
    </div>

    <!-- Career -->
    <div class="tab-pane fade {{ $tab === 'career' ? 'show active' : '' }}" id="pane-career">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card h-100"><div class="card-header">D. Employment History</div><div class="card-body">
                    @if($student->employmentHistories->isEmpty())
                        <div class="empty-state py-3"><i class="bi bi-briefcase"></i>No employment history recorded</div>
                    @else
                        @foreach($student->employmentHistories->sortByDesc('is_current') as $e)
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="small">{{ $e->job_title }} @ {{ $e->company }}</strong>
                                    @if($e->is_current)<span class="badge bg-success" style="font-size:.65rem">CURRENT</span>@endif
                                </div>
                                <div class="small text-muted">
                                    {{ $e->industry }} {{ $e->department ? '· ' . $e->department : '' }}
                                    · {{ str_replace('_', '-', ucfirst((string) $e->employment_type)) }}
                                </div>
                                <div class="small">
                                    {{ $e->start_date?->format('M Y') }} → {{ $e->is_current ? 'Present' : ($e->end_date?->format('M Y') ?? '—') }}
                                </div>
                                <div class="small">
                                    Work: {{ $e->work_phone ?: '—' }} · {{ $e->work_email ?: '—' }}
                                    @if($canSensitive && $e->salary_range) · Salary: {{ $e->salary_range }} @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div></div>
            </div>
            <div class="col-lg-5">
                <div class="card mb-3"><div class="card-header">Skills & Goals</div><div class="card-body">
                    <div class="mb-2"><div class="info-label">Professional Skills</div><div class="info-value">{{ $student->professional_skills ?: '—' }}</div></div>
                    <div class="mb-2"><div class="info-label">Technical Skills</div><div class="info-value">{{ $student->technical_skills ?: '—' }}</div></div>
                    <div class="mb-2"><div class="info-label">Certifications</div><div class="info-value">{{ $student->certifications ?: '—' }}</div></div>
                    <div class="mb-2"><div class="info-label">Career Goals</div><div class="info-value">{{ $student->career_goals ?: '—' }}</div></div>
                    <div class="mb-2"><div class="info-label">Portfolio</div><div class="info-value">
                        @if($student->portfolio_url)<a href="{{ $student->portfolio_url }}" target="_blank" rel="noopener">{{ $student->portfolio_url }}</a>@else — @endif
                    </div></div>
                    <div><div class="info-label">CV / Resume</div><div class="info-value">
                        @if($student->cv_path)<a href="{{ asset('storage/' . $student->cv_path) }}" target="_blank"><i class="bi bi-file-earmark-arrow-down me-1"></i>Download CV</a>@else — @endif
                    </div></div>
                </div></div>
                <div class="card"><div class="card-header">Career Notes</div><div class="card-body small">
                    {{ $student->career_notes ?: '—' }}
                </div></div>
            </div>
        </div>
    </div>

    <!-- Communication -->
    <div class="tab-pane fade {{ $tab === 'communication' ? 'show active' : '' }}" id="pane-communication">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Communication History (chronological)</span>
                @if(auth()->user()->canManageStudents())
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#interactionModal"><i class="bi bi-plus-lg me-1"></i>Add Interaction</button>
                @endif
            </div>
            <div class="card-body">
                @if($student->communicationLogs->isEmpty())
                    <div class="empty-state"><i class="bi bi-chat-square"></i>No communications recorded yet</div>
                @else
                    <div class="timeline">
                        @foreach($student->communicationLogs->sortByDesc('contact_datetime') as $log)
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div>
                                        <strong class="small">{{ $log->subject }}</strong>
                                        <span class="badge bg-light text-dark border ms-1" style="font-size:.68rem">
                                            {{ \App\Models\CommunicationLog::CHANNELS[$log->channel] ?? $log->channel }}
                                        </span>
                                        <span class="badge {{ $log->direction === 'incoming' ? 'bg-info' : 'bg-secondary' }}" style="font-size:.68rem">
                                            <i class="bi bi-arrow-{{ $log->direction === 'incoming' ? 'down-left' : 'up-right' }}"></i> {{ ucfirst($log->direction) }}
                                        </span>
                                    </div>
                                    <div class="small text-muted d-flex align-items-center gap-2">
                                        {{ $log->contact_datetime->format('M d, Y H:i') }} · by {{ $log->user?->name ?? 'System' }}
                                        @if(auth()->user()->canManageStudents())
                                            <form action="{{ route('communications.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('You are about to delete this **communication record**. This action cannot be undone. Would you like to proceed?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="small">{{ $log->message_summary }}</div>
                                <div class="small text-muted">
                                    Outcome: {{ $log->outcome ?: '—' }}
                                    @if($log->followup_required) · <span class="text-warning">Follow-up required{{ $log->next_followup_date ? ' by ' . $log->next_followup_date->format('M d, Y') : '' }}</span> @endif
                                </div>
                                @if($log->internal_notes)<div class="small fst-italic text-muted"><i class="bi bi-lock me-1"></i>{{ $log->internal_notes }}</div>@endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tasks -->
    <div class="tab-pane fade {{ $tab === 'tasks' ? 'show active' : '' }}" id="pane-tasks">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Follow-up Tasks</span>
                @if(auth()->user()->canManageStudents())
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#taskModal"><i class="bi bi-plus-lg me-1"></i>Schedule Follow-up</button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Title</th><th>Priority</th><th>Due</th><th>Assigned</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        @forelse($student->followUpTasks->sortByDesc('due_date') as $task)
                            <tr>
                                <td><span class="fw-semibold small">{{ $task->title }}</span><div class="text-muted small">{{ \Illuminate\Support\Str::limit($task->description, 70) }}</div></td>
                                <td><span class="badge bg-{{ ['high' => 'danger', 'medium' => 'warning', 'low' => 'secondary'][$task->priority] }}">{{ ucfirst($task->priority) }}</span></td>
                                <td class="small {{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $task->due_date->format('M d, Y') }}@if($task->isOverdue())<div style="font-size:.7rem">OVERDUE</div>@endif</td>
                                <td class="small">{{ $task->assignee?->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ ['pending' => 'secondary', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'dark'][$task->status] }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></td>
                                <td class="text-end text-nowrap">
                                    @if(auth()->user()->canManageStudents() && in_array($task->status, ['pending', 'in_progress']))
                                        <a href="{{ route('follow-ups.edit', $task) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('follow-ups.index', ['complete' => $task->id]) }}" class="btn btn-sm btn-outline-success" title="Complete task"><i class="bi bi-check2"></i></a>
                                    @else
                                        <span class="text-muted small">{{ $task->result ? 'Result: ' . \Illuminate\Support\Str::limit($task->result, 40) : '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><i class="bi bi-check2-circle"></i>No follow-up tasks</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Events -->
    <div class="tab-pane fade {{ $tab === 'events' ? 'show active' : '' }}" id="pane-events">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-header">Event Participation History</div><div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($student->eventRegistrations as $reg)
                            <a href="{{ route('events.show', $reg->event) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <strong class="small">{{ $reg->event?->title }}</strong>
                                    <span class="badge bg-{{ ['registered' => 'secondary', 'attended' => 'success', 'no_show' => 'warning', 'cancelled' => 'secondary'][$reg->status] ?? 'secondary' }}">{{ ['registered' => 'Registered', 'attended' => 'Attended', 'no_show' => 'Absent', 'cancelled' => 'Cancelled'][$reg->status] ?? ucfirst(str_replace('_', ' ', $reg->status)) }}</span>
                                </div>
                                <div class="small text-muted">{{ $reg->event?->start_datetime?->format('M d, Y H:i') }} · {{ $reg->event?->location }}</div>
                            </a>
                        @empty
                            <div class="empty-state py-4"><i class="bi bi-calendar-x"></i>No event participation yet</div>
                        @endforelse
                    </div>
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">Register for Upcoming Event</div>
                    <div class="card-body">
                        @if(auth()->user()->canManageStudents())
                            <form method="POST" action="{{ route('events.registrations.store', ['event' => '__ID__']) }}" id="eventRegForm">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <div class="mb-2">
                                    <select class="form-select form-select-sm" id="eventSelect" required>
                                        <option value="">— Select an upcoming event —</option>
                                        @foreach($upcomingEvents as $e)
                                            <option value="{{ $e->id }}" data-url="{{ route('events.registrations.store', $e) }}">{{ $e->title }} ({{ $e->start_datetime->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">Register Student</button>
                            </form>
                            <script>
                                document.getElementById('eventSelect').addEventListener('change', function () {
                                    const opt = this.options[this.selectedIndex];
                                    if (opt.dataset.url) document.getElementById('eventRegForm').action = opt.dataset.url;
                                });
                            </script>
                        @else
                            <div class="text-muted small">Only staff can register students for events.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="tab-pane fade {{ $tab === 'documents' ? 'show active' : '' }}" id="pane-documents">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Documents</span>
                @if(auth()->user()->canManageStudents())
                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal"><i class="bi bi-upload me-1"></i>Upload Document</button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Type</th><th>Uploaded</th><th>By</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        @forelse($student->documents as $doc)
                            @if($doc->is_sensitive && !auth()->user()->canViewSensitiveData())
                                <tr><td colspan="5" class="text-muted small"><i class="bi bi-lock me-1"></i>A sensitive document exists — restricted access.</td></tr>
                                @continue
                            @endif
                            <tr>
                                <td class="small fw-semibold"><i class="bi bi-file-earmark me-1"></i>{{ $doc->name }}
                                    @if($doc->is_sensitive)<span class="badge bg-danger ms-1" style="font-size:.6rem">SENSITIVE</span>@endif
                                    @if($doc->description)<div class="text-muted fw-normal">{{ $doc->description }}</div>@endif
                                </td>
                                <td class="small">{{ \App\Models\StudentDocument::TYPES[$doc->doc_type] ?? $doc->doc_type }}</td>
                                <td class="small">{{ $doc->created_at->format('M d, Y') }}</td>
                                <td class="small">{{ $doc->uploader?->name ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                    @if(auth()->user()->canManageStudents())
                                        <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('You are about to delete this **document**. This action cannot be undone. Would you like to proceed?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><i class="bi bi-folder2-open"></i>No documents uploaded</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="tab-pane fade {{ $tab === 'notes' ? 'show active' : '' }}" id="pane-notes">
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card"><div class="card-header">Add Note</div><div class="card-body">
                    @if(auth()->user()->canManageStudents())
                        <form method="POST" action="{{ route('students.notes.store', $student) }}">
                            @csrf
                            <div class="mb-2">
                                <textarea name="content" class="form-control" rows="3" placeholder="Write a note..." required>{{ old('content') }}</textarea>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_private" value="1" class="form-check-input" id="notePrivate">
                                <label class="form-check-label small" for="notePrivate">Private (admins only)</label>
                            </div>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Note</button>
                        </form>
                    @else
                        <div class="text-muted small">Read-only access.</div>
                    @endif
                </div></div>
                @if($student->internal_notes && auth()->user()->canManageStudents())
                    <div class="card mt-3"><div class="card-header">Internal Notes (record-level)</div>
                        <div class="card-body small">{{ $student->internal_notes }}</div></div>
                @endif
            </div>
            <div class="col-lg-7">
                <div class="card"><div class="card-header">Notes Timeline</div><div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($student->notes->sortByDesc('created_at') as $note)
                            @if($note->is_private && !auth()->user()->isAdmin()) @continue @endif
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">{{ $note->author?->name ?? 'System' }} · {{ $note->created_at->format('M d, Y H:i') }}
                                        @if($note->is_private)<span class="badge bg-danger ms-1" style="font-size:.6rem">PRIVATE</span>@endif
                                    </span>
                                    @if(auth()->user()->canManageStudents())
                                        <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('You are about to delete this **note**. This action cannot be undone. Would you like to proceed?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                                <div class="small">{{ $note->content }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-4"><i class="bi bi-sticky"></i>No notes yet</div>
                        @endforelse
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canManageStudents() && (auth()->user()->isAdmin() || $student->assigned_staff_id === auth()->id()))
<!-- Face Enrollment Modal -->
<div class="modal fade" id="faceEnrollmentModal" tabindex="-1" aria-labelledby="faceEnrollmentTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="faceEnrollmentForm">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <div class="modal-header">
                <h5 class="modal-title" id="faceEnrollmentTitle"><i class="bi bi-person-bounding-box me-1"></i>Enroll face — {{ $student->name_english }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">A face embedding is stored for attendance matching; the captured photo is not retained. Obtain the student's consent (or parent/guardian consent where applicable) before proceeding.</p>
                <div class="ratio ratio-16x9 bg-dark rounded overflow-hidden mb-3">
                    <video id="faceEnrollmentCamera" class="w-100 h-100" style="object-fit: cover" autoplay muted playsinline aria-label="Face enrollment camera preview"></video>
                </div>
                <div id="faceEnrollmentStatus" class="small text-muted mb-3" role="status" aria-live="polite">Start the camera and center one face in view.</div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="consent_confirmed" value="1" id="faceConsentConfirmed" required>
                    <label class="form-check-label" for="faceConsentConfirmed">I confirm the student (or parent/guardian, if applicable) has consented to face-based attendance processing.</label>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="startFaceEnrollmentCamera"><i class="bi bi-camera-video me-1"></i>Start camera</button>
                    <button type="button" class="btn btn-outline-secondary" id="stopFaceEnrollmentCamera" disabled>Stop camera</button>
                </div>
                <button type="submit" class="btn btn-primary" id="saveFaceEnrollment" disabled><i class="bi bi-check2-circle me-1"></i>Capture and enroll</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Interaction Modal -->
<div class="modal fade" id="interactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" action="{{ route('students.communications.store', $student) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-chat-dots me-1"></i>Add Interaction — {{ $student->name_english }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($student->status === 'do_not_contact')
                    <div class="alert alert-danger small"><i class="bi bi-slash-circle me-1"></i>This student is marked <strong>Do Not Contact</strong>. Outgoing communications are blocked; only incoming records may be logged.</div>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Channel <span class="required-mark">*</span></label>
                        <select name="channel" class="form-select" required>
                            @foreach(\App\Models\CommunicationLog::CHANNELS as $k => $lbl)
                                <option value="{{ $k }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Date & Time <span class="required-mark">*</span></label>
                        <input type="datetime-local" name="contact_datetime" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Direction <span class="required-mark">*</span></label>
                        <div class="mt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="direction" id="dirOut" value="outgoing" checked>
                                <label class="form-check-label" for="dirOut">Outgoing</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="direction" id="dirIn" value="incoming">
                                <label class="form-check-label" for="dirIn">Incoming</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subject <span class="required-mark">*</span></label>
                        <input type="text" name="subject" class="form-control" maxlength="255" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Message Summary</label>
                        <textarea name="message_summary" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Outcome</label>
                        <input type="text" name="outcome" class="form-control" placeholder="e.g. Positive, No answer...">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="followup_required" value="1" class="form-check-input" id="fuReq" onchange="document.getElementById('fuDate').required = this.checked">
                            <label class="form-check-label" for="fuReq">Follow-up required</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Next Follow-up</label>
                        <input type="date" name="next_followup_date" id="fuDate" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Internal Notes</label>
                        <input type="text" name="internal_notes" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Interaction</button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Follow-up Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('follow-ups.store') }}">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <input type="hidden" name="redirect_to" value="student">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-bell me-1"></i>Schedule Follow-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Task Title <span class="required-mark">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="255">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Due Date <span class="required-mark">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ now()->addWeek()->toDateString() }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="{{ auth()->id() }}">Me ({{ auth()->user()->name }})</option>
                            @if(auth()->user()->isAdmin())
                                @foreach(\App\Models\User::where('is_active', true)->where('id', '!=', auth()->id())->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Reminder (optional)</label>
                        <input type="datetime-local" name="reminder_at" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning"><i class="bi bi-calendar-check me-1"></i>Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('students.documents.store', $student) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-upload me-1"></i>Upload Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Document Name <span class="required-mark">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="255">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type <span class="required-mark">*</span></label>
                    <select name="doc_type" class="form-select" required>
                        @foreach(\App\Models\StudentDocument::TYPES as $k => $lbl)
                            <option value="{{ $k }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">File <span class="required-mark">*</span></label>
                    <input type="file" name="file" class="form-control" required>
                    <div class="form-text">Max 10MB. PDF, images, Word documents.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_sensitive" value="1" class="form-check-input" id="docSensitive">
                    <label class="form-check-label" for="docSensitive">Sensitive — restrict to admins only</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-secondary"><i class="bi bi-upload me-1"></i>Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Add / Edit Course Enrollment Modal -->
<div class="modal fade" id="enrollmentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" id="enrollmentForm" method="POST" action="{{ route('students.enrollments.store', $student) }}">
            @csrf
            <input type="hidden" name="_method" id="enrollmentMethod" value="POST">
            <input type="hidden" name="type" id="enr_type" value="main">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-mortarboard me-1"></i><span id="enrollmentModalTitle">Add Course</span> — {{ $student->name_english }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if(isset($errors) && ($errors->has('course_id') || $errors->has('enrollment_status')))
                    <div class="alert alert-danger py-2 small">
                        {{ $errors->first('course_id') }} {{ $errors->first('enrollment_status') }}
                        <button type="button" class="btn-close float-end" data-bs-dismiss="alert" style="font-size:.6rem"></button>
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Course <span class="required-mark">*</span></label>
                        <select name="course_id" id="enr_course_id" class="form-select" required>
                            <option value="">— Select course —</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" data-extra="{{ $c->isExtraCourse() ? 1 : 0 }}">{{ $c->name }}{{ $c->code ? ' (' . $c->code . ')' : '' }}{{ $c->isExtraCourse() ? ' — Supplementary' : '' }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" id="enr_course_hint"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status Remark</label>
                        <select name="enrollment_status" id="enr_status" class="form-select">
                            @foreach(\App\Models\StudentCourse::STATUS_LABELS as $k => $lbl)
                                <option value="{{ $k }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Auto-set to "Completed" if a completion/graduation date is filled and status is left as Current.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Campus</label>
                        <select name="campus_id" id="enr_campus_id" class="form-select">
                            <option value="">—</option>
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Batch</label>
                        <select name="batch_id" id="enr_batch_id" class="form-select">
                            <option value="">—</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Intake</label>
                        <select name="intake_id" id="enr_intake_id" class="form-select">
                            <option value="">—</option>
                            @foreach($intakes as $in)
                                <option value="{{ $in->id }}">{{ $in->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" id="enr_semester_id" class="form-select">
                            <option value="">—</option>
                            @foreach($semesters as $se)
                                <option value="{{ $se->id }}">{{ $se->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Registration No.</label>
                        <input type="text" name="registration_number" id="enr_registration_number" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" id="enr_grade" class="form-control" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enrollment Date</label>
                        <input type="date" name="enrollment_date" id="enr_enrollment_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="course_start_date" id="enr_course_start_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Completion Date</label>
                        <input type="date" name="course_completion_date" id="enr_course_completion_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Graduation Date</label>
                        <input type="date" name="graduation_date" id="enr_graduation_date" class="form-control">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" id="enr_notes" class="form-control" maxlength="5000">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Course</button>
            </div>
        </form>
    </div>
</div>

<script>
    const enrollmentStoreUrl = @js(route('students.enrollments.store', $student));
    const enrollmentUpdateUrl = (id) => @js(url('students')) + '/' + @js($student->id) + '/enrollments/' + id;
    function enrollmentCourseOptions(courseSelect) {
        return Array.from(courseSelect.options).filter((o) => o.value !== '');
    }
    function applyEnrollmentMode(mode, keepCourseId) {
        const courseSelect = document.getElementById('enr_course_id');
        const hint = document.getElementById('enr_course_hint');
        const wantExtra = mode === 'supplementary' ? '1' : '0';
        let visible = 0;
        enrollmentCourseOptions(courseSelect).forEach(function (o) {
            const match = o.dataset.extra === wantExtra;
            o.hidden = !match;
            o.disabled = !match;
            if (match) visible++;
        });
        const firstOption = courseSelect.options[0];
        firstOption.hidden = false;
        firstOption.disabled = false;
        if (keepCourseId) {
            courseSelect.value = String(keepCourseId);
        } else if (!enrollmentCourseOptions(courseSelect).some((o) => !o.disabled && o.value === courseSelect.value)) {
            courseSelect.value = '';
        }
        document.getElementById('enr_type').value = mode;
        document.getElementById('enrollmentModalTitle').textContent =
            mode === 'supplementary' ? 'Add Supplementary Course' : 'Add Course';
        hint.textContent = visible === 0
            ? 'No courses available in this category yet. Assign courses to a category flagged "Extra" in Academic → Course Categories to add supplementary courses.'
            : (mode === 'supplementary' ? 'Showing supplementary (extra) courses only.' : 'Showing main program courses only.');
    }
    function openEnrollmentModal(data, mode) {
        const form = document.getElementById('enrollmentForm');
        const effectiveMode = mode || (data ? (Number(data._extra) === 1 ? 'supplementary' : 'main') : 'main');
        document.getElementById('enrollmentMethod').value = data ? 'PUT' : 'POST';
        form.action = data ? enrollmentUpdateUrl(data.id) : enrollmentStoreUrl;
        applyEnrollmentMode(effectiveMode, data ? data.course_id : null);
        if (data) document.getElementById('enrollmentModalTitle').textContent = 'Edit Course';
        ['course_id','campus_id','batch_id','intake_id','semester_id','registration_number','enrollment_status','grade','notes',
         'enrollment_date','course_start_date','course_completion_date','graduation_date'].forEach(function (field) {
            const el = document.getElementById('enr_' + field);
            let value = data ? data[field] : null;
            if (value && field.endsWith('_date')) value = String(value).slice(0, 10);
            el.value = value ?? '';
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        const faceForm = document.getElementById('faceEnrollmentForm');
        const faceVideo = document.getElementById('faceEnrollmentCamera');
        const faceStatus = document.getElementById('faceEnrollmentStatus');
        const startFaceCameraButton = document.getElementById('startFaceEnrollmentCamera');
        const stopFaceCameraButton = document.getElementById('stopFaceEnrollmentCamera');
        const saveFaceButton = document.getElementById('saveFaceEnrollment');
        let faceCameraStream = null;

        function stopFaceCamera() {
            faceCameraStream?.getTracks().forEach(track => track.stop());
            faceCameraStream = null;
            faceVideo.srcObject = null;
            startFaceCameraButton.disabled = false;
            stopFaceCameraButton.disabled = true;
            saveFaceButton.disabled = true;
        }

        startFaceCameraButton?.addEventListener('click', async function () {
            if (!navigator.mediaDevices?.getUserMedia) {
                faceStatus.textContent = 'Camera access is unavailable. Use localhost or open this page over HTTPS.';
                return;
            }

            startFaceCameraButton.disabled = true;
            faceStatus.textContent = 'Requesting camera access…';

            try {
                faceCameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                faceVideo.srcObject = faceCameraStream;
                await faceVideo.play();
                stopFaceCameraButton.disabled = false;
                saveFaceButton.disabled = false;
                faceStatus.textContent = 'Camera ready. Center only the student’s face, confirm consent, and select Capture and enroll.';
            } catch (error) {
                stopFaceCamera();
                faceStatus.textContent = error.name === 'NotAllowedError'
                    ? 'Camera permission was denied. Allow camera access and try again.'
                    : 'Could not start the camera. Check that it is connected and not being used by another app.';
            }
        });

        stopFaceCameraButton?.addEventListener('click', stopFaceCamera);
        document.getElementById('faceEnrollmentModal')?.addEventListener('hidden.bs.modal', stopFaceCamera);

        faceForm?.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!faceCameraStream || !faceVideo.videoWidth || !faceVideo.videoHeight) {
                faceStatus.textContent = 'Start the camera and wait for the preview before enrolling.';
                return;
            }

            if (!document.getElementById('faceConsentConfirmed').checked) {
                faceStatus.textContent = 'Confirm consent before enrolling this face.';
                return;
            }

            saveFaceButton.disabled = true;
            faceStatus.textContent = 'Capturing and processing face…';

            try {
                const canvas = document.createElement('canvas');
                canvas.width = faceVideo.videoWidth;
                canvas.height = faceVideo.videoHeight;
                canvas.getContext('2d').drawImage(faceVideo, 0, 0, canvas.width, canvas.height);
                const image = await new Promise((resolve, reject) => {
                    canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Could not capture an image.')), 'image/jpeg', 0.9);
                });
                const formData = new FormData(faceForm);
                formData.set('image', image, 'face-enrollment.jpg');
                const response = await fetch(@json(route('face.register')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok || !result.success) {
                    throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Face enrollment failed. Ensure exactly one face is visible and try again.');
                }

                faceStatus.textContent = 'Face profile enrolled successfully. The student can now use face attendance scanning.';
                const enrollButton = document.querySelector('[data-bs-target="#faceEnrollmentModal"]');
                if (enrollButton) enrollButton.innerHTML = '<i class="bi bi-person-bounding-box me-1"></i>Update face profile';
                stopFaceCamera();
            } catch (error) {
                faceStatus.textContent = error.message || 'Face enrollment failed. Please try again.';
                saveFaceButton.disabled = !faceCameraStream;
            }
        });

        window.addEventListener('beforeunload', stopFaceCamera);

        if ({{ (isset($errors) && ($errors->has('course_id') || $errors->has('enrollment_status'))) ? 'true' : 'false' }}) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('enrollmentModal')).show();
        }
    });
</script>
@endif
@endsection
