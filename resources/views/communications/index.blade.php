@extends('layouts.app')

@section('title', 'Communications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-chat-dots text-primary me-1"></i>Communication Management</h1>
        <div class="page-subtitle">All interactions between the center and alumni</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-3">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Subject, summary, student..." value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0">Student</label>
                <select name="student_id" class="form-select form-select-sm">
                    <option value="">All students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" @selected(request('student_id') == $s->id)>{{ $s->name_english }} ({{ $s->student_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0">Channel</label>
                <select name="channel" class="form-select form-select-sm">
                    <option value="">All channels</option>
                    @foreach(\App\Models\CommunicationLog::CHANNELS as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('channel') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label mb-0">Direction</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="incoming" @selected(request('direction') === 'incoming')>Incoming</option>
                    <option value="outgoing" @selected(request('direction') === 'outgoing')>Outgoing</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0">From / To</label>
                <div class="d-flex gap-1">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('communications.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Date / Time</th><th>Student</th><th>Channel</th><th>Direction</th>
                    <th>Subject</th><th>Outcome</th><th>Follow-up</th><th>Staff</th><th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small text-nowrap">{{ $log->contact_datetime->format('M d, Y') }}<div class="text-muted" style="font-size:.72rem">{{ $log->contact_datetime->format('H:i') }}</div></td>
                        <td>
                            <a href="{{ route('students.show', [$log->student_id, 'tab' => 'communication']) }}" class="text-decoration-none fw-semibold small">
                                {{ $log->student?->name_english ?? '—' }}
                            </a>
                            <div class="text-muted" style="font-size:.72rem">{{ $log->student?->student_code }}</div>
                        </td>
                        <td class="small">{{ \App\Models\CommunicationLog::CHANNELS[$log->channel] ?? $log->channel }}</td>
                        <td>
                            <span class="badge {{ $log->direction === 'incoming' ? 'bg-info' : 'bg-secondary' }}">
                                <i class="bi bi-arrow-{{ $log->direction === 'incoming' ? 'down-left' : 'up-right' }}"></i> {{ ucfirst($log->direction) }}
                            </span>
                        </td>
                        <td class="small" style="max-width:220px">
                            <div class="fw-semibold text-truncate">{{ $log->subject }}</div>
                            <div class="text-muted text-truncate" style="font-size:.73rem">{{ $log->message_summary }}</div>
                        </td>
                        <td class="small">{{ $log->outcome ?: '—' }}</td>
                        <td class="small">
                            @if($log->followup_required)
                                <span class="text-warning"><i class="bi bi-bell-fill"></i> {{ $log->next_followup_date?->format('M d') ?? 'Required' }}</span>
                            @else — @endif
                        </td>
                        <td class="small">{{ $log->user?->name ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            @if(auth()->user()->canManageStudents())
                                <a href="{{ route('communications.edit', $log) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('communications.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('You are about to delete this **communication record**. This action cannot be undone. Would you like to proceed?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state"><i class="bi bi-chat-square"></i>No communications found</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap">
        <small class="text-muted">{{ $logs->total() }} record(s)</small>
        {{ $logs->links() }}
    </div>
</div>
@endsection
