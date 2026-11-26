@extends('layouts.app')

@section('title', 'Opportunities')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-briefcase text-primary me-1"></i>Jobs & Opportunities</h1>
        <div class="page-subtitle">Jobs, internships, scholarships, training programs and volunteer opportunities</div>
    </div>
    @if(auth()->user()->canManageStudents())
        <a href="{{ route('opportunities.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Opportunity</a>
    @endif
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Title or organization..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Type</label>
                <select name="opportunity_type" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach(\App\Models\Opportunity::TYPES as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('opportunity_type') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Publication Status</label>
                <select name="publication_status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'] as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('publication_status') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('opportunities.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Opportunity</th><th>Type</th><th>Deadline</th><th>Target</th><th>Status</th><th>Applications</th><th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($opportunities as $opp)
                    <tr>
                        <td>
                            <a href="{{ route('opportunities.show', $opp) }}" class="fw-semibold small text-decoration-none">{{ $opp->title }}</a>
                            <div class="text-muted" style="font-size:.75rem">{{ $opp->organization }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ \App\Models\Opportunity::TYPES[$opp->opportunity_type] ?? $opp->opportunity_type }}</span></td>
                        <td class="small {{ $opp->application_deadline?->isPast() ? 'text-danger' : '' }}">{{ $opp->application_deadline?->format('M d, Y') ?? '—' }}</td>
                        <td class="small">{{ $opp->target_course ?: 'Any' }}@if($opp->target_graduation_year)<div class="text-muted" style="font-size:.72rem">Years: {{ $opp->target_graduation_year }}</div>@endif</td>
                        <td>
                            @php $pc = ['draft' => 'secondary', 'published' => 'success', 'closed' => 'dark']; @endphp
                            <span class="badge bg-{{ $pc[$opp->publication_status] }}">{{ ucfirst($opp->publication_status) }}</span>
                        </td>
                        <td class="small">{{ $opp->applications_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('opportunities.show', $opp) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->canManageStudents())
                                <a href="{{ route('opportunities.match', $opp) }}" class="btn btn-sm btn-outline-info" title="Match alumni"><i class="bi bi-people"></i></a>
                                <a href="{{ route('opportunities.edit', $opp) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="bi bi-briefcase"></i>No opportunities found</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">{{ $opportunities->links() }}</div>
</div>
@endsection
