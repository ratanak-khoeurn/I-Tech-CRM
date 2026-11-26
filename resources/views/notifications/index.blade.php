@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-inbox text-primary me-1"></i>Notifications</h1>
        <div class="page-subtitle">
            In-system alerts only — Telegram / Email / SMS / WhatsApp messages are <strong>not</strong> sent automatically
            unless those services are configured and connected.
        </div>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('notifications.generate') }}">
            @csrf
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Check & Generate Alerts</button>
        </form>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-light btn-sm"><i class="bi bi-check2-all me-1"></i>Mark All Read</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $n)
                @php
                    $icons = ['followup_reminder' => 'bell', 'overdue_task' => 'exclamation-octagon', 'upcoming_event' => 'calendar-event', 'new_opportunity' => 'briefcase', 'student_update' => 'person'];
                @endphp
                <div class="list-group-item {{ $n->is_read ? '' : 'bg-primary bg-opacity-10' }} d-flex justify-content-between align-items-start gap-2">
                    <div class="d-flex gap-2">
                        <i class="bi bi-{{ $icons[$n->type] ?? 'info-circle' }} fs-5 {{ $n->type === 'overdue_task' ? 'text-danger' : 'text-primary' }}"></i>
                        <div>
                            <div class="small {{ $n->is_read ? 'text-muted' : 'fw-semibold' }}">{{ $n->title }}</div>
                            <div class="small text-muted">{{ $n->message }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $n->created_at->format('M d, Y H:i') }} · {{ $n->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-1 text-nowrap">
                        @if($n->link)
                            <a href="{{ url($n->link) }}" class="btn btn-sm btn-outline-primary">Open</a>
                        @endif
                        @unless($n->is_read)
                            <form method="POST" action="{{ route('notifications.read', $n) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-light" title="Mark read"><i class="bi bi-check2"></i></button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="empty-state"><i class="bi bi-bell-slash"></i>No notifications. Click "Check & Generate Alerts" to scan for upcoming follow-ups, overdue tasks, events and opportunities.</div>
            @endforelse
        </div>
    </div>
    <div class="card-footer bg-white">{{ $notifications->links() }}</div>
</div>
@endsection
