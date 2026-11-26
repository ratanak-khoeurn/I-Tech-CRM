@extends('layouts.app')

@section('title', 'Outreach Campaigns')

@push('styles')
<link href="{{ asset('css/kanban-board.css') }}" rel="stylesheet">
@endpush

@php
    // Toggle a chip without dropping the other filters: null/false/'' remove a param.
    $url = function (array $extra = []) {
        $params = array_merge(request()->query(), $extra);
        unset($params['page']);
        $params = array_filter($params, fn ($v) => $v !== null && $v !== '' && $v !== false);

        return route('outreach.index') . ($params === [] ? '' : '?' . http_build_query($params));
    };
    $hasFilters = $filters['q'] !== '' || $filters['purpose'] !== null || $filters['status'] !== null
        || $filters['campus'] !== null || $filters['event'] || $filters['attention'];
    $actives = array_values(array_filter($campaigns->all(), fn ($c) => $c->status === 'active'));
    $totalToContact = array_sum(array_map(fn ($c) => (int) ($c->stage_counts['to_contact'] ?? 0), $actives));
    $filterChips = array_values(array_filter([
        $filters['q'] !== '' ? 'search “' . $filters['q'] . '”' : null,
        $filters['purpose'] !== null ? $purposes[$filters['purpose']] : null,
        $filters['status'] !== null ? ucfirst($filters['status']) : null,
        $filters['campus'] !== null ? ($campuses->firstWhere('id', $filters['campus'])?->name ?? 'campus') : null,
        $filters['event'] ? 'event-linked' : null,
        $filters['attention'] ? 'needs action' : null,
    ]));
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-megaphone text-primary me-1"></i>Outreach Campaigns</h1>
        <div class="page-subtitle">Contact lists for events, opportunities, surveys and reunions — one board per campaign</div>
    </div>
    @if ($canManage)
        <a href="{{ route('outreach.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i>New Campaign
        </a>
    @endif
</div>

<div class="kb-stat-strip mb-3">
    <div class="kb-stat">
        <div class="kb-stat-num">{{ $unfilteredTotal }}</div>
        <div class="kb-stat-label">Campaign(s)</div>
        <div class="kb-stat-sub">{{ count($actives) }} active now</div>
    </div>
    <div class="kb-stat">
        <div class="kb-stat-num">{{ number_format($leadTotal) }}</div>
        <div class="kb-stat-label">People tracked</div>
        <div class="kb-stat-sub">across all campaigns</div>
    </div>
    <div class="kb-stat">
        <div class="kb-stat-num">{{ number_format($totalToContact) }}</div>
        <div class="kb-stat-label">Waiting to contact</div>
        <div class="kb-stat-sub">
            <i class="bi bi-arrow-right-short align-middle"></i><a href="{{ $url(['attention' => '1', 'status' => 'active']) }}"
                class="text-decoration-none">see the boards</a>
        </div>
    </div>
    <div class="kb-stat">
        <div class="kb-stat-num">{{ $leadTotal > 0 ? round($handledTotal / $leadTotal * 100) : 0 }}%</div>
        <div class="kb-stat-label">Decided</div>
        <div class="progress kb-stat-bar">
            <div class="progress-bar bg-success" style="width: {{ $leadTotal > 0 ? round($handledTotal / $leadTotal * 100) : 0 }}%"></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('outreach.index') }}" class="kb-filterbar">
            <span class="kb-filter-label"><i class="bi bi-funnel"></i>Filter</span>
            <div class="kb-search">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm"
                    placeholder="Campaign, note or linked event">
                <button type="button" class="btn-close js-clear-filter" aria-label="Clear"></button>
            </div>
            <select name="purpose" class="form-select form-select-sm">
                <option value="">All purposes</option>
                @foreach ($purposes as $value => $label)
                    <option value="{{ $value }}" @selected($filters['purpose'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                <option value="completed" @selected($filters['status'] === 'completed')>Completed</option>
                <option value="archived" @selected($filters['status'] === 'archived')>Archived</option>
            </select>
            <select name="campus_id" class="form-select form-select-sm">
                <option value="">All campuses</option>
                @foreach ($campuses as $campus)
                    <option value="{{ $campus->id }}" @selected($filters['campus'] === (int) $campus->id)>{{ $campus->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-funnel me-1"></i>Apply</button>
            @if ($hasFilters)
                <a href="{{ route('outreach.index') }}" class="btn btn-light btn-sm border rounded-pill">Clear</a>
            @endif
        </form>

        <div class="kb-chips">
            <span class="kb-chips-label">Quick</span>
            <a class="kb-chip {{ $filters['purpose'] === null && $filters['status'] === null && !$filters['event'] && !$filters['attention'] ? 'is-on' : '' }}"
                href="{{ $url(['purpose' => null, 'status' => null, 'event' => null, 'attention' => null, 'q' => null]) }}">
                All <b>{{ number_format($unfilteredTotal) }}</b>
            </a>
            @foreach ($purposes as $value => $label)
                <a class="kb-chip {{ $filters['purpose'] === $value ? 'is-on' : '' }}"
                    href="{{ $url(['purpose' => $filters['purpose'] === $value ? null : $value]) }}">
                    <i class="bi {{ ['event' => 'bi-calendar-event', 'opportunity' => 'bi-briefcase', 'survey' => 'bi-pencil-square', 'announcement' => 'bi-megaphone', 'reunion' => 'bi-people', 'other' => 'bi-three-dots'][$value] ?? 'bi-tag' }}"></i>{{ $label }}<b>{{ $purposeTotals[$value] ?? 0 }}</b>
                </a>
            @endforeach
            <a class="kb-chip {{ $filters['event'] ? 'is-on' : '' }}" href="{{ $url(['event' => $filters['event'] ? null : '1']) }}">
                <i class="bi bi-calendar-check"></i>Linked to event
            </a>
            <a class="kb-chip {{ $filters['attention'] ? 'is-on' : '' }}" href="{{ $url(['attention' => $filters['attention'] ? null : '1', 'status' => $filters['attention'] ? null : 'active']) }}">
                <i class="bi bi-exclamation-circle"></i>Needs action<b>{{ number_format($toContactTotal) }}</b>
            </a>
            @foreach (['active' => 'bi-broadcast', 'completed' => 'bi-check2-circle', 'archived' => 'bi-archive'] as $value => $icon)
                <a class="kb-chip {{ $filters['status'] === $value ? 'is-on' : '' }}"
                    href="{{ $url(['status' => $filters['status'] === $value ? null : $value]) }}">
                    <i class="bi {{ $icon }}"></i>{{ ucfirst($value) }}<b>{{ $statusTotals[$value] ?? 0 }}</b>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
    <span>{{ $campaigns->count() }} campaign(s) shown
        @if ($hasFilters)of {{ number_format($unfilteredTotal) }}@endif
    </span>
    @if ($hasFilters)
        <span><i class="bi bi-funnel-fill me-1"></i>{{ implode(' · ', $filterChips) }}</span>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Purpose</th>
                        <th>Linked event</th>
                        <th>Progress</th>
                        <th>Contact stages</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td>
                                <a href="{{ route('outreach.board', $campaign) }}" class="fw-semibold small text-decoration-none">{{ $campaign->name }}</a>
                                <div class="text-muted small">
                                    {{ $campaign->leads_count }} person(s)
                                    @if ($campaign->creator) · {{ $campaign->creator->name }} @endif
                                </div>
                            </td>
                            <td class="small">{{ $campaign->purposeLabel() }}</td>
                            <td class="small">
                                @if ($campaign->event)
                                    <a href="{{ route('events.show', $campaign->event) }}" class="text-decoration-none">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $campaign->event->title }}
                                    </a>
                                    <div class="text-muted">{{ $campaign->event->start_datetime?->format('Y-m-d') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="min-width:130px">
                                <div class="progress" style="height:6px">
                                    <div class="progress-bar bg-success" style="width: {{ $campaign->progressPercentage() }}%"></div>
                                </div>
                                <div class="text-muted small">{{ $campaign->progressPercentage() }}% handled</div>
                            </td>
                            <td>
                                @php($filled = array_filter($stages, fn ($label, $stage) => (int) ($campaign->stage_counts[$stage] ?? 0) > 0, ARRAY_FILTER_USE_BOTH))
                                @if ($filled === [])
                                    <span class="badge rounded-pill text-bg-light border text-muted">no cards yet</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($filled as $stage => $label)
                                            <span class="badge rounded-pill"
                                                style="background: var(--bs-{{ $colors[$stage] }})">
                                                {{ $label }}: {{ $campaign->stage_counts[$stage] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $campaign->status === 'active' ? 'primary' : ($campaign->status === 'completed' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('outreach.board', $campaign) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">Board</a>
                                @if ($canManage)
                                    <a href="{{ route('outreach.edit', $campaign) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                                @endif
                                @if (auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('outreach.destroy', $campaign) }}" class="d-inline"
                                        onsubmit="return confirm('Remove campaign &quot;{{ $campaign->name }}&quot; and its contact board? The students themselves are not touched.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                @if ($hasFilters)
                                    <div class="mb-2"><i class="bi bi-search fs-4 d-block opacity-50"></i></div>
                                    No campaign matches those filters.
                                    <div class="mt-2">
                                        <a href="{{ route('outreach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Clear filters
                                        </a>
                                    </div>
                                @else
                                    No campaign yet. @if ($canManage)<a href="{{ route('outreach.create') }}">Create the first one</a>. @endif
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-light border mt-3 small mb-0">
    <i class="bi bi-info-circle me-1 text-primary"></i>
    A campaign board tracks contact status per person for that campaign only. It is separate from the student
    Pipeline Board, which holds one global enrollment stage per student.
</div>
@endsection
