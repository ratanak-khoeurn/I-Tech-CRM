@extends('layouts.app')

@section('title', 'Follow-up Calendar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar3 text-primary me-1"></i>Follow-up Calendar</h1>
        <div class="page-subtitle">{{ $month->format('F Y') }}</div>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-primary"><i class="bi bi-list-ul me-1"></i>List</a>
            <a href="{{ route('follow-ups.calendar') }}" class="btn btn-primary"><i class="bi bi-calendar3 me-1"></i>Calendar</a>
        </div>
        <div class="btn-group btn-group-sm">
            <a class="btn btn-light" href="{{ route('follow-ups.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"><i class="bi bi-chevron-left"></i></a>
            <a class="btn btn-light" href="{{ route('follow-ups.calendar', ['month' => now()->format('Y-m')]) }}">Today</a>
            <a class="btn btn-light" href="{{ route('follow-ups.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-1 text-center small fw-bold text-muted mb-1">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)
                <div class="col">{{ $d }}</div>
            @endforeach
        </div>
        @php $day = $start->copy(); @endphp
        @while($day <= $end)
            @if($day->dayOfWeek === 1 || $day->equalTo($start))<div class="row g-1 mb-1">@endif
            @php
                $key = $day->format('Y-m-d');
                $dayTasks = $tasksByDate[$key] ?? collect();
            @endphp
            <div class="col">
                <div class="calendar-day {{ $day->month !== $month->month ? 'other-month' : '' }} {{ $day->isToday() ? 'today' : '' }}">
                    <div class="small fw-bold mb-1">{{ $day->day }}</div>
                    @foreach($dayTasks as $t)
                        <a href="{{ route('students.show', [$t->student_id, 'tab' => 'tasks']) }}"
                           class="calendar-task bg-{{ ['high' => 'danger', 'medium' => 'warning', 'low' => 'secondary'][$t->priority] }} text-white text-decoration-none"
                           title="{{ $t->title }} — {{ $t->student?->name_english }}">
                            {{ $t->title }}
                        </a>
                    @endforeach
                </div>
            </div>
            @if($day->dayOfWeek === 0 || $day->equalTo($end))</div>@endif
            @php $day->addDay(); @endphp
        @endwhile
    </div>
    <div class="card-footer bg-white small text-muted">
        <span class="badge bg-danger me-1">&nbsp;</span>High priority
        <span class="badge bg-warning ms-2 me-1">&nbsp;</span>Medium
        <span class="badge bg-secondary ms-2 me-1">&nbsp;</span>Low
        — showing open tasks only
    </div>
</div>
@endsection
