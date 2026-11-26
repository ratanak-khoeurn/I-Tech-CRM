@extends('layouts.app')

@php $filters = old('audience', $campaign->audience_filters ?? []); @endphp
@php $selectedFlags = old('audience.flags', $filters['flags'] ?? []); @endphp

@section('title', $campaign->exists ? 'Edit Campaign' : 'New Outreach Campaign')

@section('content')
<div class="mb-3">
    <h1 class="page-title">
        <i class="bi bi-megaphone text-primary me-1"></i>{{ $campaign->exists ? 'Edit Campaign' : 'New Outreach Campaign' }}
    </h1>
    <div class="page-subtitle">
        {{ $campaign->exists ? $campaign->name : 'Name it, then build the list of people to contact' }}
    </div>
</div>

<form method="POST"
    action="{{ $campaign->exists ? route('outreach.update', $campaign) : route('outreach.store') }}"
    class="row g-4">
    @csrf
    @if ($campaign->exists) @method('PUT') @endif

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Campaign</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="255"
                        value="{{ old('name', $campaign->name) }}"
                        placeholder="e.g. Alumni Reunion 2026 — invitation wave 1">
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                        <select name="purpose" class="form-select" required>
                            @foreach ($purposes as $key => $label)
                                <option value="{{ $key }}" @selected(old('purpose', $campaign->purpose) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach (['active' => 'Active', 'completed' => 'Completed', 'archived' => 'Archived'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $campaign->status) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Linked alumni event</label>
                    <select name="alumni_event_id" class="form-select">
                        <option value="">Not tied to an event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((int) old('alumni_event_id', $campaign->alumni_event_id) === (int) $event->id)>
                                {{ $event->title }} — {{ $event->start_datetime?->format('Y-m-d') }} ({{ $event->status }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        When linked, the <strong>Registered</strong> column fills itself from that event's
                        registration list — no manual dragging needed.
                    </div>
                    @error('alumni_event_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label class="form-label">Notes for the team</label>
                    <textarea name="description" rows="3" class="form-control"
                        maxlength="5000" placeholder="Talking points, who calls whom, deadline...">{{ old('description', $campaign->description) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Audience filters</div>
            <div class="card-body">
                <div class="text-muted small mb-3">
                    These filters decide who can be pulled onto the board with one click.
                    @if ($campaign->exists)
                        Right now <strong>{{ $audienceCount ?? 0 }}</strong> student(s) match.
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Course</label>
                        <select name="audience[course_id]" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((int) ($filters['course_id'] ?? 0) === (int) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Campus</label>
                        <select name="audience[campus_id]" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach ($campuses as $campus)
                                <option value="{{ $campus->id }}" @selected((int) ($filters['campus_id'] ?? 0) === (int) $campus->id)>{{ $campus->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Graduation year</label>
                        <select name="audience[graduation_year]" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach ($gradYears as $year)
                                <option value="{{ $year }}" @selected((string) ($filters['graduation_year'] ?? '') === (string) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Student stage</label>
                        <select name="audience[student_stage]" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach ($studentStages as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['student_stage'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Graduation</label>
                        <select name="audience[graduation_status]" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach ($graduationStatuses as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['graduation_status'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Engagement</label>
                        <select name="audience[engagement_status]" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach (['engaged' => 'Engaged', 'semi_engaged' => 'Semi-engaged', 'not_engaged' => 'Not engaged'] as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['engagement_status'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Alumni group</label>
                        <input type="text" name="audience[alumni_group]" class="form-control form-control-sm"
                            value="{{ $filters['alumni_group'] ?? '' }}" placeholder="e.g. Class of 2024 IT">
                    </div>
                </div>

                <div class="form-label small mt-3 mb-1">Only people who said yes to…</div>
                <div class="row g-1">
                    @foreach ($flagLabels as $flag => $label)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="audience[flags][{{ $flag }}]"
                                    value="1" id="flag-{{ $flag }}" @checked(!empty($selectedFlags[$flag]))>
                                <label class="form-check-label small" for="flag-{{ $flag }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">
                    Students marked "Do Not Contact" are always skipped.
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-check2-circle me-1"></i>{{ $campaign->exists ? 'Save changes' : 'Create campaign' }}
            </button>
            <a href="{{ $campaign->exists ? route('outreach.board', $campaign) : route('outreach.index') }}"
                class="btn btn-light btn-sm border">Cancel</a>
        </div>
    </div>
</form>
@endsection
