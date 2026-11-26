@extends('layouts.app')

@section('title', $event->exists ? 'Edit Event' : 'Create Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-event text-primary me-1"></i>
                {{ $event->exists ? 'Edit Event — ' . $event->title : 'Create Alumni Event' }}
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" data-loading
                      action="{{ $event->exists ? route('events.update', $event) : route('events.store') }}">
                    @csrf
                    @if($event->exists) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Event Title <span class="required-mark">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $event->title) }}" required maxlength="255">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Event Type <span class="required-mark">*</span></label>
                            <select name="event_type" class="form-select" required>
                                <option value="">— Select —</option>
                                @foreach(\App\Models\AlumniEvent::TYPES as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('event_type', $event->event_type) === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $event->description) }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date & Time <span class="required-mark">*</span></label>
                            <input type="datetime-local" name="start_datetime" class="form-control" value="{{ old('start_datetime', $event->start_datetime?->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date & Time</label>
                            <input type="datetime-local" name="end_datetime" class="form-control" value="{{ old('end_datetime', $event->end_datetime?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $event->location) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Registration Deadline</label>
                            <input type="date" name="registration_deadline" class="form-control" value="{{ old('registration_deadline', $event->registration_deadline?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Participants</label>
                            <input type="number" name="max_participants" class="form-control" min="1" value="{{ old('max_participants', $event->max_participants) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Organizer</label>
                            <input type="text" name="organizer" class="form-control" value="{{ old('organizer', $event->organizer ?: \App\Models\Setting::get('center_name')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Target Alumni Group</label>
                            <input type="text" name="target_group" class="form-control" value="{{ old('target_group', $event->target_group) }}" placeholder="e.g. Class of 2023, All alumni">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status <span class="required-mark">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['draft' => 'Draft', 'published' => 'Published', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('status', $event->status) === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Event Photo / Document</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            @if($event->photo)<div class="form-text">Current: {{ basename($event->photo) }}</div>@endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Event Feedback (after completion)</label>
                            <textarea name="feedback" class="form-control" rows="2">{{ old('feedback', $event->feedback) }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ $event->exists ? route('events.show', $event) : route('events.index') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>{{ $event->exists ? 'Update Event' : 'Create Event' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
