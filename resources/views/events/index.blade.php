@extends('layouts.app')

@section('title', 'Alumni Events')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar-event text-primary me-1"></i>Alumni Events & Activities</h1>
        <div class="page-subtitle">Reunions, workshops, career talks, networking, training, volunteering and mentorship</div>
    </div>
    @if(auth()->user()->canManageStudents())
        <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Create Event</a>
    @endif
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Event title or location..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Type</label>
                <select name="event_type" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach(\App\Models\AlumniEvent::TYPES as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('event_type') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(['draft' => 'Draft', 'published' => 'Published', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('events.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($events as $event)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                @if($event->photo)
                    <img src="{{ asset('storage/' . $event->photo) }}" class="card-img-top" height="140" style="object-fit:cover" alt="">
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="card-title mb-0"><a href="{{ route('events.show', $event) }}" class="text-decoration-none">{{ $event->title }}</a></h6>
                        @php $sc = ['draft' => 'secondary', 'published' => 'primary', 'ongoing' => 'info', 'completed' => 'success', 'cancelled' => 'danger']; @endphp
                        <span class="badge bg-{{ $sc[$event->status] }}">{{ ucfirst($event->status) }}</span>
                    </div>
                    <div class="small text-muted mb-2">
                        <span class="badge bg-light text-dark border">{{ \App\Models\AlumniEvent::TYPES[$event->event_type] ?? $event->event_type }}</span>
                    </div>
                    <div class="small mb-1"><i class="bi bi-calendar3 text-primary me-1"></i>{{ $event->start_datetime->format('M d, Y H:i') }}
                        @if($event->end_datetime) — {{ $event->end_datetime->format('H:i') }} @endif
                    </div>
                    <div class="small mb-1"><i class="bi bi-geo-alt text-primary me-1"></i>{{ $event->location ?: 'TBA' }}</div>
                    <div class="small mb-2"><i class="bi bi-people text-primary me-1"></i>{{ $event->registrations_count }} registered
                        @if($event->max_participants) / {{ $event->max_participants }} seats @endif
                    </div>
                    <p class="small text-muted mb-0">{{ \Illuminate\Support\Str::limit($event->description, 90) }}</p>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Details</a>
                    @if(auth()->user()->canManageStudents())
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card"><div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-1">No events found</p>
                    @if(auth()->user()->canManageStudents())
                        <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm mt-2"><i class="bi bi-plus-lg me-1"></i>Create the first event</a>
                    @endif
                </div>
            </div></div>
        </div>
    @endforelse
</div>

<div class="mt-3">{{ $events->links() }}</div>
@endsection
