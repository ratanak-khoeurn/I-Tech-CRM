@extends('layouts.app')

@section('title', $opportunity->title)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('opportunities.index') }}">Opportunities</a></li>
        <li class="breadcrumb-item active">{{ $opportunity->title }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title">{{ $opportunity->title }}</h1>
        <div class="page-subtitle">{{ \App\Models\Opportunity::TYPES[$opportunity->opportunity_type] ?? $opportunity->opportunity_type }} @ {{ $opportunity->organization }}</div>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->canManageStudents())
            <a href="{{ route('opportunities.match', $opportunity) }}" class="btn btn-info btn-sm text-white"><i class="bi bi-people me-1"></i>Match Eligible Alumni</a>
            <a href="{{ route('opportunities.edit', $opportunity) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('opportunities.destroy', $opportunity) }}" onsubmit="return confirm('You are about to delete this **opportunity**. This action cannot be undone. Would you like to proceed?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">Details</div>
            <div class="card-body">
                @php $pc = ['draft' => 'secondary', 'published' => 'success', 'closed' => 'dark']; @endphp
                <span class="badge bg-{{ $pc[$opportunity->publication_status] }} mb-2">{{ ucfirst($opportunity->publication_status) }}</span>
                <p class="small">{{ $opportunity->description }}</p>
                @if($opportunity->requirements)
                    <div class="mb-2"><div class="info-label">Requirements</div><div class="info-value" style="white-space:pre-line">{{ $opportunity->requirements }}</div></div>
                @endif
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted" style="width:42%">Deadline</td><td class="{{ $opportunity->application_deadline?->isPast() ? 'text-danger fw-bold' : '' }}">{{ $opportunity->application_deadline?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Contact</td><td>{{ $opportunity->contact_info ?: '—' }}</td></tr>
                    <tr><td class="text-muted">Apply Online</td><td>@if($opportunity->application_url)<a href="{{ $opportunity->application_url }}" target="_blank" rel="noopener">{{ $opportunity->application_url }}</a>@else — @endif</td></tr>
                    <tr><td class="text-muted">Target Course</td><td>{{ $opportunity->target_course ?: 'Any course' }}</td></tr>
                    <tr><td class="text-muted">Target Grad Years</td><td>{{ $opportunity->target_graduation_year ?: 'Any year' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text text-primary me-1"></i>Applications & Referrals ({{ $opportunity->applications->count() }})</span>
                @if(auth()->user()->canManageStudents())
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyModal"><i class="bi bi-plus-lg me-1"></i>Add / Refer Alumni</button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead><tr><th>Alumni</th><th>Source</th><th>Status</th><th>Notes</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        @forelse($opportunity->applications as $app)
                            <tr>
                                <td>
                                    <a href="{{ route('students.show', $app->student_id) }}" class="small fw-semibold text-decoration-none">{{ $app->student?->name_english }}</a>
                                    <div class="text-muted" style="font-size:.72rem">{{ $app->student?->student_code }}</div>
                                </td>
                                <td class="small">{{ ucfirst(str_replace('_', ' ', $app->source)) }}</td>
                                <td>
                                    @if(auth()->user()->canManageStudents())
                                        <form method="POST" action="{{ route('opportunities.applications.update', $app) }}">
                                            @csrf @method('PATCH')
                                            <select name="status" class="form-select form-select-sm py-0" style="font-size:.75rem" onchange="this.form.submit()">
                                                @foreach(['interested' => 'Interested', 'applied' => 'Applied', 'interviewing' => 'Interviewing', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn'] as $k => $lbl)
                                                    <option value="{{ $k }}" @selected($app->status === $k)>{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($app->status) }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ \Illuminate\Support\Str::limit($app->notes, 40) }}</td>
                                <td class="text-end">
                                    @if(auth()->user()->canManageStudents())
                                        <form method="POST" action="{{ route('opportunities.applications.destroy', $app) }}" onsubmit="return confirm('You are about to remove this **application**. Would you like to proceed?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state py-3"><i class="bi bi-inbox"></i>No applications yet — use Match to find eligible alumni</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canManageStudents())
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('opportunities.applications.store', $opportunity) }}">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i>Add Application / Referral</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Alumni <span class="required-mark">*</span></label>
                    <select name="student_id" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach($availableStudents as $s)
                            <option value="{{ $s->id }}">{{ $s->name_english }} ({{ $s->student_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Source <span class="required-mark">*</span></label>
                    <select name="source" class="form-select" required>
                        <option value="referred">Referred by staff</option>
                        <option value="matched">Matched by system</option>
                        <option value="self_applied">Self-applied</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="interested">Interested</option>
                        <option value="applied">Applied</option>
                        <option value="interviewing">Interviewing</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
