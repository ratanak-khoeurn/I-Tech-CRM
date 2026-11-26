@extends('layouts.app')

@section('title', $event->title)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('events.index') }}">Events</a></li>
        <li class="breadcrumb-item active">{{ $event->title }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title">{{ $event->title }}</h1>
        <div class="page-subtitle">
            {{ \App\Models\AlumniEvent::TYPES[$event->event_type] ?? $event->event_type }}
            · Created by {{ $event->creator?->name ?? '—' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->canManageStudents())
            @php $outreachCampaign = \App\Models\OutreachCampaign::where('alumni_event_id', $event->id)->first(); @endphp
            @if($outreachCampaign)
                <a href="{{ route('outreach.board', $outreachCampaign) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-megaphone me-1"></i>Contact board
                </a>
            @endif
            <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('You are about to delete this **event** along with all of its registrations. This action cannot be undone. Would you like to proceed?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card mb-3">
            @if($event->photo)<img src="{{ asset('storage/' . $event->photo) }}" class="card-img-top" height="200" style="object-fit:cover; padding: 20px;" alt="">@endif
            <div class="card-body">
                @php $sc = ['draft' => 'secondary', 'published' => 'primary', 'ongoing' => 'info', 'completed' => 'success', 'cancelled' => 'danger']; @endphp
                <span class="badge bg-{{ $sc[$event->status] }} mb-2">{{ ucfirst($event->status) }}</span>
                <p class="small">{{ $event->description }}</p>
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted" style="width:42%">Date & Time</td><td>{{ $event->start_datetime->format('M d, Y H:i') }}@if($event->end_datetime) — {{ $event->end_datetime->format('M d, Y H:i') }}@endif</td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $event->location ?: 'TBA' }}</td></tr>
                    <tr><td class="text-muted">Registration Deadline</td><td>{{ $event->registration_deadline?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Participants</td><td>{{ $event->registrations->count() }}@if($event->max_participants) / {{ $event->max_participants }} ({{ $event->seatsLeft() }} seats left)@endif</td></tr>
                    <tr><td class="text-muted">Organizer</td><td>{{ $event->organizer ?: '—' }}</td></tr>
                    <tr><td class="text-muted">Target Group</td><td>{{ $event->target_group ?: 'All alumni' }}</td></tr>
                </table>
                @if($event->feedback)
                    <div class="alert alert-info small mt-3 mb-0"><strong>Event Feedback:</strong><br>{{ $event->feedback }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            @php
                $regByStatus = $event->registrations->countBy('status');
                $regTotal = $event->registrations->count();
                $attendedCount = $regByStatus['attended'] ?? 0;
                $absentCount = $regByStatus['no_show'] ?? 0;
            @endphp
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="fw-semibold"><i class="bi bi-people text-primary me-1"></i>Registration List ({{ $regTotal }})</span>
                    @if($regTotal > 0)
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            <span class="badge text-bg-success"><i class="bi bi-check2 me-1"></i>Attended {{ $attendedCount }}</span>
                            <span class="badge text-bg-warning">Absent {{ $absentCount }}</span>
                        </div>
                    @endif
                </div>
                @if(auth()->user()->canManageStudents() && $event->status !== 'completed' && $event->status !== 'cancelled')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="bi bi-person-plus me-1"></i>Register Alumni</button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead><tr><th>Alumni</th><th>Registered</th><th>Attendance Status</th><th>Feedback</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        @forelse($event->registrations as $reg)
                            <tr>
                                <td>
                                    <a href="{{ route('students.show', $reg->student_id) }}" class="small fw-semibold text-decoration-none">{{ $reg->student?->name_english }}</a>
                                    <div class="text-muted" style="font-size:.72rem">{{ $reg->student?->student_code }}</div>
                                </td>
                                <td class="small">{{ $reg->created_at->format('M d, Y') }}<div class="text-muted" style="font-size:.7rem">by {{ $reg->registeredBy?->name ?? '—' }}</div></td>
                                <td>
                                    @if(auth()->user()->canManageStudents())
                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                            @php
                                                $attendanceLabels = ['registered' => 'Registered', 'attended' => 'Attended', 'no_show' => 'Absent', 'cancelled' => 'Cancelled'];
                                                $attendanceColors = ['registered' => 'secondary', 'attended' => 'success', 'no_show' => 'warning', 'cancelled' => 'secondary'];
                                            @endphp
                                            <span class="badge text-bg-{{ $attendanceColors[$reg->status] ?? 'secondary' }}">{{ $attendanceLabels[$reg->status] ?? ucfirst(str_replace('_', ' ', $reg->status)) }}</span>
                                            @if($reg->status !== 'attended')
                                                <form method="POST" action="{{ route('events.registrations.update', $reg) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="attended">
                                                    <button class="btn btn-sm btn-outline-success py-0 px-2">Attended</button>
                                                </form>
                                            @endif
                                            @if($reg->status !== 'no_show')
                                                <form method="POST" action="{{ route('events.registrations.update', $reg) }}">
                                                @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="no_show">
                                                    <button class="btn btn-sm btn-outline-warning py-0 px-2">Absent</button>
                                                </form>
                                            @endif
                                        </div>
                                        @if($reg->status === 'attended' && $reg->attended_at)
                                            <div class="text-muted" style="font-size:.68rem"><i class="bi bi-clock-history me-1"></i>Checked in {{ $reg->attended_at->format('M d, Y H:i') }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $reg->status)) }}</span>
                                        @if($reg->status === 'attended' && $reg->attended_at)
                                            <div class="text-muted" style="font-size:.68rem">{{ $reg->attended_at->format('M d, Y H:i') }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->canManageStudents())
                                        <form method="POST" action="{{ route('events.registrations.update', $reg) }}" class="d-flex gap-1">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $reg->status }}">
                                            <input type="text" name="feedback" class="form-control form-control-sm py-0" style="font-size:.75rem" value="{{ $reg->feedback }}" placeholder="Feedback...">
                                            <button type="button" class="btn btn-sm btn-outline-primary align-self-center h-100 py-0 px-1"><i class="bi bi-check2"></i></button>
                                        </form>
                                    @else
                                        <span class="small">{{ $reg->feedback ?: '—' }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(auth()->user()->canManageStudents())
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            @if($reg->student?->email)
                                                <form method="POST" action="{{ route('events.registrations.email', $reg) }}">
                                                    @csrf
                                                    <button class="btn btn-sm py-0 px-1 {{ $reg->confirmation_sent_at ? 'btn-outline-success' : 'btn-outline-primary' }}"
                                                            title="{{ $reg->confirmation_sent_at ? 'Resend' : 'Send' }} confirmation email to {{ $reg->student->email }}">
                                                        <i class="bi bi-envelope-arrow-up"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="btn btn-sm btn-outline-secondary py-0 px-1 disabled" title="No email on student profile"><i class="bi bi-envelope-slash"></i></span>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                                    data-bs-toggle="modal" data-bs-target="#removeRegistrationModal"
                                                    data-remove-url="{{ route('events.registrations.destroy', $reg) }}"
                                                    data-student-name="{{ $reg->student?->name_english }}"
                                                    title="Remove registration" aria-label="Remove registration for {{ $reg->student?->name_english }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        @if($reg->confirmation_sent_at)
                                            <div class="text-success"><i class="bi bi-check2 me-1"></i>Emailed {{ $reg->confirmation_sent_at->format('M d, H:i') }}</div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state py-3"><i class="bi bi-person-x"></i>No registrations yet</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canManageStudents())
<div class="modal fade" id="removeRegistrationModal" tabindex="-1" aria-labelledby="removeRegistrationModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="removeRegistrationForm" class="modal-content" data-confirm-skip>
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title" id="removeRegistrationModalTitle"><i class="bi bi-person-x text-danger me-2"></i>Remove Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Remove <strong id="removeRegistrationStudent">this student</strong> from <strong>{{ $event->title }}</strong>?
                <div class="small text-muted mt-2">This will remove their event registration and attendance record.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Remove Registration</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('removeRegistrationModal')?.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    document.getElementById('removeRegistrationForm').action = trigger.dataset.removeUrl;
    document.getElementById('removeRegistrationStudent').textContent = trigger.dataset.studentName || 'this student';
});
</script>
@endif

@if(auth()->user()->canManageStudents())
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('events.registrations.store', $event) }}">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i>Register Alumni</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Select Alumni <span class="required-mark">*</span></label>
                <select name="student_id" class="form-select" required>
                    <option value="">— Select —</option>
                    @foreach($availableStudents as $s)
                        <option value="{{ $s->id }}">{{ $s->name_english }} ({{ $s->student_code }})</option>
                    @endforeach
                </select>
                @if($event->registration_deadline && $event->registration_deadline->isPast())
                    <div class="alert alert-warning small mt-2 mb-0">Registration deadline has passed.</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Register</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
