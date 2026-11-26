@extends('layouts.app')

@section('title', 'Students / Alumni')

@push('styles')
<style>
    .student-index-page .student-index-hero {
        position: relative;
        overflow: hidden;
        padding: 1.35rem 1.5rem;
        border: 0;
        border-radius: 1rem;
        color: #fff;
        background: linear-gradient(120deg, #123f70 0%, #1565c0 58%, #3989d5 100%);
        box-shadow: 0 12px 28px rgba(21, 101, 192, .18);
    }
    .student-index-page .student-index-hero::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: 12%;
        top: -145px;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 50%;
        box-shadow: 0 0 0 28px rgba(255,255,255,.045), 0 0 0 58px rgba(255,255,255,.035);
        pointer-events: none;
    }
    .student-index-page .student-index-hero-content { position: relative; z-index: 1; }
    .student-index-page .student-index-hero .page-title { color: #fff; font-size: 1.45rem; }
    .student-index-page .student-index-hero .page-subtitle { color: rgba(255,255,255,.76); }
    .student-index-page .student-count {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .7rem;
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        color: #fff;
        font-size: .78rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .student-index-page .student-index-hero .btn-light { color: #174d84; font-weight: 600; }
    .student-index-page .student-index-hero .btn-outline-light { border-color: rgba(255,255,255,.55); }
    .student-index-page .student-filter-card,
    .student-index-page .student-table-card { border-radius: .9rem; overflow: visible; }
    .student-index-page .student-filter-card .card-body { padding: 1rem 1.1rem; }
    .student-index-page .filter-heading {
        display: flex;
        align-items: center;
        gap: .55rem;
        color: #1e3a5f;
        font-size: .88rem;
        font-weight: 700;
    }
    .student-table-card{
        height: 500px;
    }
    .student-index-page .filter-heading i {
        display: inline-grid;
        width: 1.8rem;
        height: 1.8rem;
        place-items: center;
        border-radius: .55rem;
        background: #eaf3fc;
        color: #1565c0;
    }
    .student-index-page .form-control,
    .student-index-page .form-select { border-color: #dce5ef; }
    .student-index-page .form-control:focus,
    .student-index-page .form-select:focus {
        border-color: #7db4e8;
        box-shadow: 0 0 0 .2rem rgba(21,101,192,.1);
    }
    .student-index-page .student-list-toolbar { min-height: 38px; }
    .student-index-page .student-table-card { box-shadow: 0 5px 18px rgba(15,23,42,.055); }
    .student-index-page .student-list-table { min-width: 1040px; }
    .student-index-page .student-list-table thead th { padding: .8rem .75rem; }
    .student-index-page .student-list-table tbody td { padding: .78rem .75rem; }
    .student-index-page .student-list-table tbody tr { transition: background-color .16s ease; }
    .student-index-page .student-list-table tbody tr:hover { background: #f3f8fe; }
    .student-index-page .student-table-responsive.student-actions-open { overflow: visible !important; }
    .student-index-page .student-list-table tbody tr.student-action-row-open { position: relative; z-index: 1080; }
    .student-index-page .student-action-row-open .student-action-cell { position: relative; z-index: 1081; }
    .student-index-page .student-avatar {
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        object-fit: cover;
        box-shadow: 0 0 0 3px #edf5fd;
    }
    .student-index-page .student-avatar-fallback {
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(145deg, #d9ebfc, #b9d9f7);
        color: #155da3;
        box-shadow: 0 0 0 3px #edf5fd;
        font-size: .95rem;
    }
    .student-index-page .student-name { color: #173b63; white-space: nowrap; }
    .student-index-page .student-name:hover { color: #1565c0; }
    .student-index-page .student-code { letter-spacing: .025em; }
    .student-index-page .student-contact { color: #334155; font-size: .79rem; }
    .student-index-page .student-contact i { color: #7690aa; }
    .student-index-page .student-index-badge { border-radius: 999px; padding: .38em .66em; }
    .student-index-page .student-actions-toggle {
        display: inline-grid;
        width: 2.15rem;
        height: 2.15rem;
        place-items: center;
        border-radius: .65rem;
        color: #486581;
        transition: color .15s ease, background-color .15s ease, border-color .15s ease;
    }
    .student-index-page .student-actions-toggle:hover,
    .student-index-page .student-actions-toggle[aria-expanded="true"] {
        color: #155da3;
        border-color: #b9d7f2 !important;
        background: #eaf4fd;
    }
    .student-index-page .dropdown-menu { z-index: 1090; min-width: 12rem; padding: .4rem; border-color: #e5edf5; box-shadow: 0 10px 28px rgba(15,23,42,.12); }
    .student-index-page .dropdown-item { border-radius: .4rem; padding: .48rem .6rem; font-size: .84rem; }
    .student-index-page .dropdown-item i { width: 1rem; text-align: center; }
    .student-index-page .dropdown-item:focus { background: #eaf4fd; color: #155da3; }
    .student-index-page .student-table-footer { border-top: 1px solid #edf1f5; border-radius: 0 0 .9rem .9rem; }
    .student-card-preview-frame { display: block; width: 100%; height: 560px; border: 0; background: #edf1f5; }
    #studentCardModal .modal-content { overflow: hidden; border: 0; border-radius: 1rem; box-shadow: 0 20px 60px rgba(15, 35, 60, .22); }
    #studentCardModal .modal-header { border-bottom-color: #edf1f5; }
    .student-profile-preview-frame { display: block; width: 100%; height: min(72vh, 760px); min-height: 420px; border: 0; background: #fff; }
    #studentProfileModal .modal-content { overflow: hidden; border: 0; border-radius: 1rem; box-shadow: 0 20px 60px rgba(15, 35, 60, .22); }
    #studentProfileModal .modal-header { border-bottom-color: #edf1f5; }
    @media (max-width: 575.98px) {
        .student-index-page .student-index-hero { padding: 1.1rem; }
        .student-index-page .student-index-hero .page-title { font-size: 1.2rem; }
        .student-index-page .student-index-hero-actions { width: 100%; }
        .student-index-page .student-index-hero-actions .btn { flex: 1; }
    }
</style>
@endpush

@section('content')
<div class="student-index-page">
<div class="student-index-hero mb-3">
    <div class="student-index-hero-content d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="page-title"><i class="bi bi-people me-1"></i>Students / Alumni</h1>
                <span class="student-count"><i class="bi bi-person-lines-fill"></i>{{ number_format($students->total()) }} {{ request('archived') ? 'archived' : 'records' }}</span>
            </div>
            <div class="page-subtitle">Manage student profiles, graduation progress and alumni engagement.</div>
        </div>
        <div class="student-index-hero-actions d-flex gap-2 flex-wrap">
            @if(auth()->user()->canManageStudents())
                <a href="{{ route('students.import.form') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-upload me-1"></i>Import CSV</a>
                <a href="{{ route('students.duplicates') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-files me-1"></i>Duplicates</a>
                <a href="{{ route('students.create') }}" class="btn btn-light btn-sm"><i class="bi bi-person-plus me-1"></i>Add Student</a>
            @endif
        </div>
    </div>
</div>

<div class="card student-filter-card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="filter-heading"><i class="bi bi-sliders2"></i>Find and filter records</div>
            <span class="small text-muted d-none d-sm-inline">Refine the list using the fields below</span>
        </div>
        <form method="GET" action="{{ route('students.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Name, ID, phone, email, course, company..." value="{{ $filters['q'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Course</label>
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All courses</option>
                    @foreach($courses as $c)<option value="{{ $c->id }}" @selected(($filters['course_id'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Campus</label>
                <select name="campus_id" class="form-select form-select-sm">
                    <option value="">All campuses</option>
                    @foreach($campuses as $c)<option value="{{ $c->id }}" @selected(($filters['campus_id'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label mb-0">Grad Year</label>
                <select name="graduation_year" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($gradYears as $y)<option value="{{ $y }}" @selected(($filters['graduation_year'] ?? '') == $y)>{{ $y }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Employment</label>
                <select name="employment_status" class="form-select form-select-sm">
                    <option value="">Any status</option>
                    @foreach(\App\Models\Student::EMPLOYMENT_STATUS_LABELS as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['employment_status'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Student Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any status</option>
                    @foreach(\App\Models\Student::STATUS_LABELS as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Lifecycle Stage</label>
                <select name="student_stage" class="form-select form-select-sm">
                    <option value="">All stages</option>
                    @foreach(\App\Models\Student::STAGE_LABELS as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['student_stage'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Graduation Status</label>
                <select name="graduation_status" class="form-select form-select-sm">
                    <option value="">Any outcome</option>
                    @foreach(\App\Models\Student::GRADUATION_STATUS_LABELS as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['graduation_status'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Gender</label>
                <select name="gender" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="male" @selected(($filters['gender'] ?? '') === 'male')>Male</option>
                    <option value="female" @selected(($filters['gender'] ?? '') === 'female')>Female</option>
                    <option value="other" @selected(($filters['gender'] ?? '') === 'other')>Other</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Further Education</label>
                <select name="education_status" class="form-select form-select-sm">
                    <option value="">Any status</option>
                    @foreach(\App\Models\Student::EDUCATION_STATUS_LABELS as $k => $v)
                        <option value="{{ $k }}" @selected(($filters['education_status'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Assigned Staff</label>
                <select name="assigned_staff_id" class="form-select form-select-sm">
                    <option value="">Anyone</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}" @selected(($filters['assigned_staff_id'] ?? '') == $s->id)>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            @endif
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Sort By</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                    <option value="registered_date" @selected(($filters['sort'] ?? '') === 'registered_date')>Registration Date</option>
                    <option value="graduation_date" @selected(($filters['sort'] ?? '') === 'graduation_date')>Graduation/Creation Date</option>
                    <option value="last_contacted" @selected(($filters['sort'] ?? '') === 'last_contacted')>Last Contacted</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label mb-0">Order</label>
                <select name="dir" class="form-select form-select-sm">
                    <option value="asc" @selected(($filters['dir'] ?? '') === 'asc')>Asc</option>
                    <option value="desc" @selected(($filters['dir'] ?? '') === 'desc')>Desc</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-0">Students per page</label>
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="10" @selected(($filters['per_page'] ?? '10') === '10')>10 students</option>
                    <option value="25" @selected(($filters['per_page'] ?? '10') === '25')>25 students</option>
                    <option value="50" @selected(($filters['per_page'] ?? '10') === '50')>50 students</option>
                    <option value="all" @selected(($filters['per_page'] ?? '10') === 'all')>All students</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <div class="form-check align-self-center">
                    <input class="form-check-input" type="checkbox" name="archived" value="1" id="archivedChk" @checked($filters['archived'] ?? false) onchange="this.form.submit()">
                    <label class="form-check-label small" for="archivedChk">Archived</label>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('students.index') }}" class="btn btn-light btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="student-list-toolbar d-flex justify-content-between align-items-center mb-2 gap-2">
    <div class="text-muted small">
        @if(auth()->user()->isAdmin())
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="bulkAssignBtn" disabled>
                <i class="bi bi-person-gear me-1"></i>Bulk Assign to Staff
            </button>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('students.pipeline') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="bi bi-kanban me-1"></i>Pipeline Board</a>
        <a href="{{ route('students.export', request()->query()) }}" class="btn btn-outline-success btn-sm rounded-pill px-3"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV</a>
    </div>
</div>

<div class="card student-table-card">
    <div class="card-body p-0">
        <div class="table-responsive student-table-responsive">
            <table class="table table-hover align-middle mb-0 student-list-table">
                <thead>
                    <tr>
                        @if(auth()->user()->isAdmin())<th style="width:34px"><input type="checkbox" class="form-check-input" id="checkAll"></th>@endif
                        <th>Student</th>
                        <th>Contact</th>
                        <th>Course / Grad Year</th>
                        <th>Employment</th>
                        <th>Stage / Contact</th>
                        <th>Assigned To</th>
                        <th>Last Contact</th>
                        <th>Club Student?</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $s)
                        @php
                            $courseEnrollments = $s->studentCourses->sortBy(fn ($enrollment) => [
                                $enrollment->enrollment_status === 'in_progress' ? 0 : 1,
                                -($enrollment->graduation_year ?? 0),
                            ]);
                            $emp = $s->employmentHistories->firstWhere('is_current', true);
                        @endphp
                        <tr>
                            @if(auth()->user()->isAdmin())
                                <td><input type="checkbox" class="form-check-input row-check" name="student_ids[]" value="{{ $s->id }}" form="bulkAssignForm"></td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($s->photo)
                                        <img src="{{ asset('storage/' . $s->photo) }}" class="rounded-circle student-avatar" alt="">
                                    @else
                                        <span class="student-avatar-fallback d-inline-flex align-items-center justify-content-center fw-bold">{{ strtoupper(substr($s->name_english, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <a href="{{ route('students.show', $s) }}" class="fw-semibold text-decoration-none student-name">{{ $s->name_english }}</a>
                                        @if($s->name_khmer)<div class="text-muted" style="font-size:.75rem">{{ $s->name_khmer }}</div>@endif
                                        <div class="text-muted student-code" style="font-size:.72rem">{{ $s->student_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="student-contact"><i class="bi bi-telephone me-1"></i>{{ $s->phone }}</div>
                                @if($s->email)<div class="student-contact text-muted text-truncate" style="max-width:160px"><i class="bi bi-envelope me-1"></i>{{ $s->email }}</div>@endif
                            </td>
                            <td style="font-size:.8rem">
                                @forelse($courseEnrollments as $enrollment)
                                    @php
                                        $courseInvoice = $s->invoices
                                            ->filter(fn ($invoice) => (int) $invoice->student_course_id === (int) $enrollment->id
                                                || (!$invoice->student_course_id && $enrollment->course_id
                                                    && (int) $invoice->feeStructure?->course_id === (int) $enrollment->course_id))
                                            ->sortByDesc('id')
                                            ->first();
                                        $coursePayment = !$courseInvoice
                                            ? ['No invoice', 'secondary']
                                            : match (true) {
                                                $courseInvoice->status === 'cancelled' => ['Invoice voided', 'secondary'],
                                                $courseInvoice->total() > 0 && $courseInvoice->paidAmount() >= $courseInvoice->total() => ['Paid', 'success'],
                                                $courseInvoice->paidAmount() > 0 => ['Partially paid', 'warning'],
                                                $courseInvoice->status === 'overdue' => ['Not paid · overdue', 'danger'],
                                                $courseInvoice->status === 'draft' => ['Not paid · invoice pending', 'secondary'],
                                                default => ['Not paid', 'danger'],
                                            };
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-start gap-2 {{ !$loop->last ? 'mb-1 pb-1 border-bottom' : '' }}">
                                        <div>
                                            <div>{{ $enrollment->course?->name ?? $enrollment->center_name ?? 'Unknown course' }}</div>
                                            @if($enrollment->graduation_year)
                                                <div class="text-muted" style="font-size:.73rem">Class of {{ $enrollment->graduation_year }}</div>
                                            @endif
                                            <span class="badge text-bg-{{ $coursePayment[1] }} mt-1" style="font-size:.6rem">Payment: {{ $coursePayment[0] }}</span>
                                        </div>
                                        <span class="badge {{ $enrollment->statusBadgeClass }} text-nowrap" style="font-size:.6rem">{{ $enrollment->statusLabel }}</span>
                                    </div>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td style="font-size:.8rem">
                                @if($emp)
                                    {{ $emp->job_title }}
                                    <div class="text-muted" style="font-size:.73rem">{{ $emp->company }}</div>
                                @else
                                    <span class="text-muted">{{ \App\Models\Student::EMPLOYMENT_STATUS_LABELS[$s->employment_status] ?? '—' }}</span>
                                @endif
                            </td>
                            <td>
                                @php $statusColors = ['active_alumni' => 'success', 'inactive' => 'warning', 'unreachable' => 'secondary', 'do_not_contact' => 'danger']; @endphp
                                <span class="badge bg-primary badge-soft student-index-badge">{{ \App\Models\Student::STAGE_LABELS[$s->student_stage] ?? $s->student_stage }}</span><br>
                                <span class="badge bg-{{ $s->graduation_status === 'graduated' ? 'success' : 'secondary' }} badge-soft student-index-badge">{{ \App\Models\Student::GRADUATION_STATUS_LABELS[$s->graduation_status] ?? 'Graduation status not set' }}</span><br>
                                <span class="badge bg-{{ $statusColors[$s->status] ?? 'secondary' }} badge-soft student-index-badge">{{ \App\Models\Student::STATUS_LABELS[$s->status] ?? $s->status }}</span>
                                @if($s->record_status === 'archived')<span class="badge bg-dark ms-1">Archived</span>@endif
                            </td>
                            <td style="font-size:.8rem">{{ $s->assignedStaff?->name ?? '—' }}</td>
                            <td style="font-size:.78rem">
                                {{ $s->last_contacted_date ? $s->last_contacted_date->format('M d, Y') : '—' }}
                                @if($s->next_followup_date)
                                    <div class="text-muted" style="font-size:.72rem">Next: {{ $s->next_followup_date->format('M d') }}</div>
                                @endif
                            </td>
                            <td style="font-size:.8rem">
                                @if($s->schoolClubs->isNotEmpty())
                                    <span class="badge bg-success badge-soft student-index-badge">Yes</span>
                                @else
                                    <span class="badge bg-secondary badge-soft student-index-badge">No</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap student-action-cell">
                                <div class="dropdown student-action-dropdown">
                                    <button class="btn btn-sm btn-light border student-actions-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions for {{ $s->name_english }}" title="More actions">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('students.show', $s) }}"><i class="bi bi-eye me-2"></i>View profile</a></li>
                                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#studentCardModal" data-card-url="{{ route('students.card', ['student' => $s, 'preview' => 1]) }}" data-student-name="{{ $s->name_english }}"><i class="bi bi-person-vcard me-2"></i>Generate student card</button></li>
                                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#studentProfileModal" data-profile-url="{{ route('students.profile-export', ['student' => $s, 'preview' => 1]) }}" data-student-name="{{ $s->name_english }}"><i class="bi bi-printer me-2"></i>Export profile</button></li>
                                        @if(auth()->user()->isAdmin() && $s->record_status === 'active')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="{{ route('invoices.index', ['student_id' => $s->id]) }}"><i class="bi bi-credit-card me-2"></i>Payments &amp; invoices</a></li>
                                            <li><a class="dropdown-item" href="{{ route('invoices.create', ['student_id' => $s->id]) }}"><i class="bi bi-receipt me-2"></i>Create invoice</a></li>
                                        @endif
                                        @if(auth()->user()->canManageStudents())
                                            <li><a class="dropdown-item" href="{{ route('students.edit', $s) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            @if($s->graduation_status !== 'graduated')
                                                <li><a class="dropdown-item" href="{{ route('students.edit', ['student' => $s, 'student_stage' => 'alumni', 'graduation_status' => 'graduated']) }}"><i class="bi bi-mortarboard me-2"></i>Mark Alumni + Graduated</a></li>
                                            @endif
                                            @if($s->student_stage !== 'alumni' || $s->graduation_status !== 'not_graduated')
                                                <li><a class="dropdown-item" href="{{ route('students.edit', ['student' => $s, 'student_stage' => 'alumni', 'graduation_status' => 'not_graduated']) }}"><i class="bi bi-people me-2"></i>Alumni + Not Graduated</a></li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            @if($s->record_status === 'archived' || $s->trashed())
                                                <li>
                                                    <form action="{{ route('students.restore', $s) }}" method="POST">
                                                        @csrf
                                                        <button class="dropdown-item text-success"><i class="bi bi-arrow-counterclockwise me-2"></i>Restore</button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('students.destroy', $s) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" data-confirm="Archive {{ $s->name_english }}? The record can be restored later."><i class="bi bi-archive me-2"></i>Archive</button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <p class="mb-1">No students found</p>
                                <p class="small">Try adjusting filters, or @if(auth()->user()->canManageStudents())<a href="{{ route('students.create') }}">add a new student</a> / <a href="{{ route('students.import.form') }}">import from CSV</a>.@else contact an administrator. @endif</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white student-table-footer">
        {{ $students->links() }}
    </div>
</div>

<div class="modal fade" id="studentCardModal" tabindex="-1" aria-labelledby="studentCardModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="studentCardModalTitle"><i class="bi bi-person-vcard text-primary me-2"></i>Student Card</h5>
                    <div class="small text-muted" id="studentCardModalName">Preview and print the card</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="student-card-preview-frame" id="studentCardPreviewFrame" title="Student card preview" src="about:blank"></iframe>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="studentCardModalPrint"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="studentProfileModal" tabindex="-1" aria-labelledby="studentProfileModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="studentProfileModalTitle"><i class="bi bi-file-earmark-person text-primary me-2"></i>Student Profile Export</h5>
                    <div class="small text-muted" id="studentProfileModalName">Preview and print the profile</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="student-profile-preview-frame" id="studentProfilePreviewFrame" title="Student profile export preview" src="about:blank"></iframe>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="studentProfileModalPrint"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<form method="POST" action="{{ route('students.bulk-assign') }}" id="bulkAssignForm" class="d-none">
    @csrf
    <input type="hidden" name="staff_id" id="bulkStaffId">
</form>

<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Bulk Assign Staff</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="small text-muted">Assign <span id="bulkCount" class="fw-bold">0</span> selected student(s) to:</p>
                <select class="form-select form-select-sm" id="bulkStaffSelect">
                    <option value="">— Select staff —</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->role?->display_name }})</option>@endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="bulkAssignConfirm" disabled>Assign</button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cardModal = document.getElementById('studentCardModal');
    const cardFrame = document.getElementById('studentCardPreviewFrame');
    const cardName = document.getElementById('studentCardModalName');
    const cardPrintButton = document.getElementById('studentCardModalPrint');
    const profileModal = document.getElementById('studentProfileModal');
    const profileFrame = document.getElementById('studentProfilePreviewFrame');
    const profileName = document.getElementById('studentProfileModalName');
    const profilePrintButton = document.getElementById('studentProfileModalPrint');

    cardModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        cardFrame.src = trigger.dataset.cardUrl;
        cardName.textContent = trigger.dataset.studentName || 'Preview and print the card';
    });

    cardModal?.addEventListener('hidden.bs.modal', () => {
        cardFrame.src = 'about:blank';
        cardName.textContent = 'Preview and print the card';
    });

    cardPrintButton?.addEventListener('click', () => {
        cardFrame.contentWindow?.focus();
        cardFrame.contentWindow?.print();
    });

    profileModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        profileFrame.src = trigger.dataset.profileUrl;
        profileName.textContent = trigger.dataset.studentName || 'Preview and print the profile';
    });

    profileModal?.addEventListener('hidden.bs.modal', () => {
        profileFrame.src = 'about:blank';
        profileName.textContent = 'Preview and print the profile';
    });

    profilePrintButton?.addEventListener('click', () => {
        profileFrame.contentWindow?.focus();
        profileFrame.contentWindow?.print();
    });

    document.addEventListener('show.bs.dropdown', (event) => {
        const dropdown = event.target.closest('.student-action-dropdown');
        if (!dropdown) return;

        dropdown.closest('tr')?.classList.add('student-action-row-open');
        dropdown.closest('.student-table-responsive')?.classList.add('student-actions-open');
    });

    document.addEventListener('hidden.bs.dropdown', (event) => {
        const dropdown = event.target.closest('.student-action-dropdown');
        if (!dropdown) return;

        dropdown.closest('tr')?.classList.remove('student-action-row-open');
        dropdown.closest('.student-table-responsive')?.classList.remove('student-actions-open');
    });
});
</script>
@if(auth()->user()->isAdmin())
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkAll = document.getElementById('checkAll');
    const checks = () => document.querySelectorAll('.row-check');
    const btn = document.getElementById('bulkAssignBtn');
    const refresh = () => {
        const n = document.querySelectorAll('.row-check:checked').length;
        btn.disabled = n === 0;
        btn.innerHTML = `<i class="bi bi-person-gear me-1"></i>Bulk Assign (${n})`;
    };
    checkAll?.addEventListener('change', () => checks().forEach(c => c.checked = checkAll.checked) || refresh());
    checks().forEach(c => c.addEventListener('change', refresh));

    const modal = new bootstrap.Modal('#bulkAssignModal');
    btn.addEventListener('click', () => {
        document.getElementById('bulkCount').textContent = document.querySelectorAll('.row-check:checked').length;
        modal.show();
    });
    const sel = document.getElementById('bulkStaffSelect');
    const confirmBtn = document.getElementById('bulkAssignConfirm');
    sel.addEventListener('change', () => confirmBtn.disabled = !sel.value);
    confirmBtn.addEventListener('click', () => {
        document.getElementById('bulkStaffId').value = sel.value;
        document.getElementById('bulkAssignForm').submit();
    });
});
</script>
@endif
@endpush
