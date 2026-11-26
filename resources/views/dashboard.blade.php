@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-speedometer2 text-primary me-1"></i>Dashboard</h1>
        <div class="page-subtitle">Overview of student lifecycle, campuses, courses, and income</div>
    </div>
    @if(auth()->user()->canManageStudents())
        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Add Student</a>
    @endif
</div>

<!-- Stat cards -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">${{ number_format($stats['income'], 2) }}</div><div class="stat-label">Total Income</div></div>
                <i class="bi bi-cash-stack stat-icon"></i>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card green h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">{{ number_format($stats['campuses']) }}</div><div class="stat-label">Campuses</div></div>
                <i class="bi bi-building stat-icon"></i>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card orange h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">{{ number_format($stats['current_students']) }}</div><div class="stat-label">Current Students</div></div>
                <i class="bi bi-person-video3 stat-icon"></i>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card blue h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">{{ number_format($stats['alumni']) }}</div><div class="stat-label">Alumni</div></div>
                <i class="bi bi-people-fill stat-icon"></i>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card purple h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">{{ number_format($stats['courses']) }}</div><div class="stat-label">Total Courses</div></div>
                <i class="bi bi-journal-bookmark-fill stat-icon"></i>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card teal h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">{{ number_format($stats['graduates']) }}</div><div class="stat-label">Graduated Alumni</div></div>
                <i class="bi bi-mortarboard-fill stat-icon"></i>
            </div>
        </div></div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card red h-100"><div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-value">{{ number_format($stats['dropouts']) }}</div><div class="stat-label">Drop Out Students</div></div>
                <i class="bi bi-person-x-fill stat-icon"></i>
            </div>
        </div></div>
    </div>
</div>

<!-- Charts row 1 -->
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart text-primary me-1"></i>Alumni by Graduation Year</span>
            </div>
            <div class="card-body"><canvas id="chartGradYear" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart text-primary me-1"></i>Employment Status</div>
            <div class="card-body"><canvas id="chartEmployment" height="168"></canvas></div>
        </div>
    </div>
</div>

<!-- Charts row 2 -->
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-journal-text text-primary me-1"></i>Students by Course / Program</div>
            <div class="card-body"><canvas id="chartCourse" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-heart text-primary me-1"></i>Alumni Engagement</div>
            <div class="card-body"><canvas id="chartEngagement" height="168"></canvas></div>
        </div>
    </div>
</div>

<!-- Lists row -->
<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell text-primary me-1"></i>Upcoming Follow-up Tasks</span>
                <a href="{{ route('follow-ups.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <div class="card-body p-0">
                @if($overdueCount > 0)
                    <div class="alert alert-danger m-2 mb-0 py-2 small">
                        <i class="bi bi-exclamation-octagon me-1"></i><strong>{{ $overdueCount }}</strong> overdue task(s)!
                        <a href="{{ route('follow-ups.index', ['filter' => 'overdue']) }}" class="alert-link">Review</a>
                    </div>
                @endif
                <div class="list-group list-group-flush">
                    @forelse($upcomingTasks as $task)
                        <a href="{{ route('students.show', $task->student_id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold small">{{ $task->title }}</span>
                                <span class="badge {{ $task->due_date->isPast() ? 'bg-danger' : ($task->due_date->isToday() ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ $task->due_date->isToday() ? 'Today' : $task->due_date->format('M d') }}
                                </span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-person me-1"></i>{{ $task->student?->name_english }}
                                <span class="badge bg-light text-dark border ms-1">{{ ucfirst($task->priority) }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state py-4"><i class="bi bi-check2-circle"></i>No pending follow-up tasks</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-chat-dots text-primary me-1"></i>Recent Interactions</span>
                <a href="{{ route('communications.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentLogs as $log)
                        <a href="{{ route('students.show', [$log->student_id, 'tab' => 'communication']) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold small">{{ $log->subject }}</span>
                                <span class="text-muted" style="font-size:.72rem">{{ $log->contact_datetime->diffForHumans() }}</span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-person me-1"></i>{{ $log->student?->name_english }}
                                · <i class="bi bi-{{ $log->direction === 'incoming' ? 'arrow-down-left' : 'arrow-up-right' }}"></i>
                                {{ \App\Models\CommunicationLog::CHANNELS[$log->channel] ?? $log->channel }}
                            </div>
                        </a>
                    @empty
                        <div class="empty-state py-4"><i class="bi bi-chat-square"></i>No interactions recorded yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event text-primary me-1"></i>Upcoming Alumni Events</span>
                <a href="{{ route('events.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($upcomingEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold small">{{ $event->title }}</span>
                                <span class="badge bg-primary-soft bg-light text-dark border">{{ $event->start_datetime->format('M d, Y') }}</span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-geo-alt me-1"></i>{{ $event->location ?: 'TBA' }}
                                · {{ \App\Models\AlumniEvent::TYPES[$event->event_type] ?? $event->event_type }}
                            </div>
                        </a>
                    @empty
                        <div class="empty-state py-4"><i class="bi bi-calendar-x"></i>No upcoming events</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @if(auth()->user()->canManageStudents())
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-journal-plus text-primary me-1"></i>Recent Enrollments</span>
                    <a href="{{ route('students.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentEnrollments as $enrollment)
                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold small">{{ $enrollment->student?->name_english ?: $enrollment->student?->name_khmer ?: '—' }}</span>
                                    <span class="text-muted" style="font-size:.72rem">{{ $enrollment->enrollment_date?->format('M d, Y') }}</span>
                                </div>
                                <div class="small text-muted text-truncate">
                                    <i class="bi bi-journal-bookmark me-1"></i>{{ $enrollment->course?->name ?: 'No course' }}
                                    <span class="badge {{ $enrollment->statusBadgeClass }} ms-1">{{ $enrollment->statusLabel }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="empty-state py-4"><i class="bi bi-journal-x"></i>No enrollments recorded yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@php
    $gradYearData = ['labels' => array_keys($byGradYear->all()), 'values' => array_values($byGradYear->all())];
    $courseData = ['labels' => array_keys($byCourse->all()), 'values' => array_values($byCourse->all())];
    $empData = [
        'labels' => array_map(fn($k) => \App\Models\Student::EMPLOYMENT_STATUS_LABELS[$k] ?? ucfirst($k), array_keys($byEmployment->all())),
        'values' => array_values($byEmployment->all()),
    ];
    $engData = [
        'labels' => array_map(fn($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($byEngagement->all())),
        'values' => array_values($byEngagement->all()),
    ];
@endphp
<script>
const gradYearData = @json($gradYearData);
const courseData = @json($courseData);
const empData = @json($empData);
const engData = @json($engData);

new Chart(document.getElementById('chartGradYear'), {
    type: 'bar',
    data: { labels: gradYearData.labels, datasets: [{ label: 'Alumni', data: gradYearData.values, backgroundColor: '#1565c0', borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('chartCourse'), {
    type: 'bar',
    data: { labels: courseData.labels, datasets: [{ label: 'Students', data: courseData.values, backgroundColor: '#42a5f5', borderRadius: 6 }] },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('chartEmployment'), {
    type: 'doughnut',
    data: { labels: empData.labels, datasets: [{ data: empData.values, backgroundColor: ['#1565c0','#16a34a','#dc2626','#ea580c','#7c3aed','#0d9488'] }] },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
});
new Chart(document.getElementById('chartEngagement'), {
    type: 'pie',
    data: { labels: engData.labels, datasets: [{ data: engData.values, backgroundColor: ['#1565c0','#f59e0b','#94a3b8'] }] },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
});
</script>
@endpush
