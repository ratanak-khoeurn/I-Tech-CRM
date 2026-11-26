@extends('layouts.app')

@section('title', 'Student Movements')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-arrow-left-right text-primary me-1"></i>Student Movements</h1>
        <div class="page-subtitle">Enrollment, transfer, deferral and withdrawal history</div>
    </div>
    <a href="{{ route('student-movements.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Record Movement</a>
</div>

@include('academic._tabs')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Search student</label>
                <input type="text" name="q" class="form-control" placeholder="Name or code" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Movement type</label>
                <select name="movement_type" class="form-select">
                    <option value="">All</option>
                    @foreach($types as $k => $label)
                        <option value="{{ $k }}" @selected(request('movement_type') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('student-movements.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Student</th><th>Type</th><th>Detail</th><th>Reason</th><th>By</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td class="text-nowrap">{{ $m->effective_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="fw-semibold">
                            @if($m->student)
                                <a href="{{ route('students.show', $m->student) }}" class="text-decoration-none">{{ $m->student->name_english ?? $m->student->name_khmer }}</a>
                                <div class="text-muted small">{{ $m->student->student_code }}</div>
                            @else — @endif
                        </td>
                        <td>
                            @php $tb = ['enrollment'=>'success','transfer'=>'primary','batch_change'=>'info','campus_change'=>'info','deferral'=>'warning','withdrawal'=>'danger','reinstatement'=>'success','status_change'=>'secondary'][$m->movement_type] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $tb }}">{{ $m->typeLabel() }}</span>
                        </td>
                        <td class="small">
                            @if($m->from_batch_id || $m->to_batch_id)
                                {{ $m->fromBatch->name ?? '—' }} → {{ $m->toBatch->name ?? '—' }}
                            @elseif($m->from_campus_id || $m->to_campus_id)
                                {{ $m->fromCampus->name ?? '—' }} → {{ $m->toCampus->name ?? '—' }}
                            @elseif($m->from_status || $m->to_status)
                                {{ \App\Models\Student::STATUS_LABELS[$m->from_status] ?? $m->from_status ?? '—' }} → {{ \App\Models\Student::STATUS_LABELS[$m->to_status] ?? $m->to_status ?? '—' }}
                            @else — @endif
                        </td>
                        <td class="small text-muted">{{ $m->reason ?? '—' }}</td>
                        <td class="small">{{ $m->creator->name ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('student-movements.destroy', $m) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete this **movement record**. Please note that the student data will not be reverted. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state py-5"><i class="bi bi-arrow-left-right"></i><p class="mb-0">No movements recorded yet.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
        <div class="card-footer bg-transparent">{{ $movements->links() }}</div>
    @endif
</div>
@endsection
