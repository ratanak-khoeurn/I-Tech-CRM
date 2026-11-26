@extends('layouts.app')

@section('title', 'Match Alumni — ' . $opportunity->title)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('opportunities.index') }}">Opportunities</a></li>
        <li class="breadcrumb-item"><a href="{{ route('opportunities.show', $opportunity) }}">{{ $opportunity->title }}</a></li>
        <li class="breadcrumb-item active">Match</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-people text-info me-1"></i>Match Eligible Alumni</h1>
        <div class="page-subtitle">{{ $opportunity->title }} — matched by interests, course, graduation year and skills</div>
    </div>
    <a href="{{ route('opportunities.show', $opportunity) }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2 small">
        <strong>Matching criteria applied:</strong>
        <span class="badge bg-light text-dark border ms-1">Interest: {{ ucfirst(str_replace('_', ' ', $opportunity->opportunity_type)) }}</span>
        @if($opportunity->target_course)<span class="badge bg-light text-dark border ms-1">Course code: {{ $opportunity->target_course }}</span>@endif
        @if($opportunity->target_graduation_year)<span class="badge bg-light text-dark border ms-1">Grad years: {{ $opportunity->target_graduation_year }}</span>@endif
        @if($keywords)<span class="badge bg-light text-dark border ms-1">Skill keywords: {{ implode(', ', $keywords) }}</span>@endif
        <span class="badge bg-light text-dark border ms-1">Excludes Do-Not-Contact and already-applied alumni</span>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Alumni</th><th>Course / Grad Year</th><th>Current Status</th><th>Skills</th><th>Match Score</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @forelse($matches as $m)
                    @php $s = $m['student']; $sc = $s->latestStudentCourse(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('students.show', $s) }}" class="small fw-semibold text-decoration-none">{{ $s->name_english }}</a>
                            <div class="text-muted" style="font-size:.72rem">{{ $s->student_code }} · {{ $s->phone }}</div>
                        </td>
                        <td class="small">{{ $sc?->course?->name ?? '—' }}<div class="text-muted" style="font-size:.72rem">{{ $sc?->graduation_year }}</div></td>
                        <td class="small">
                            {{ \App\Models\Student::EMPLOYMENT_STATUS_LABELS[$s->employment_status] ?? '—' }}
                            @if($s->is_seeking_employment)<span class="badge bg-success ms-1" style="font-size:.62rem">SEEKING</span>@endif
                        </td>
                        <td class="small" style="max-width:220px"><span class="text-muted">{{ \Illuminate\Support\Str::limit($s->technical_skills ?: $s->professional_skills, 60) }}</span></td>
                        <td>
                            @php $pct = min(100, $m['score'] * 10); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;min-width:60px">
                                    <div class="progress-bar bg-{{ $pct >= 60 ? 'success' : ($pct >= 30 ? 'warning' : 'secondary') }}" style="width:{{ max(8, $pct) }}%"></div>
                                </div>
                                <small>{{ $m['score'] }}</small>
                            </div>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('opportunities.applications.store', $opportunity) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $s->id }}">
                                <input type="hidden" name="source" value="matched">
                                <input type="hidden" name="status" value="interested">
                                <button class="btn btn-sm btn-outline-primary" data-confirm="Refer {{ $s->name_english }} to this opportunity?">
                                    <i class="bi bi-send me-1"></i>Refer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-search"></i>No eligible alumni matched. Try broadening the target course/year in the opportunity settings.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
