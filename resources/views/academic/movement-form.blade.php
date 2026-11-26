@extends('layouts.app')

@section('title', 'Record Student Movement')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-arrow-left-right text-primary me-1"></i>Record Student Movement</h1>
    <div class="page-subtitle">Log an enrollment, transfer, deferral or withdrawal and apply it to the student</div>
</div>

@include('academic._tabs')

<div class="card mx-auto">
    <div class="card-body">
        <form method="POST" action="{{ route('student-movements.store') }}" data-loading>
            @csrf
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label">Student <span class="text-danger">*</span></label>
                    <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                        <option value="">Select student…</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" @selected((int) old('student_id', $student->id ?? 0) === $s->id)>
                                {{ $s->name_english ?: $s->name_khmer }} ({{ $s->student_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Movement type <span class="text-danger">*</span></label>
                    <select name="movement_type" id="movement_type" class="form-select @error('movement_type') is-invalid @enderror" required>
                        @foreach($types as $k => $label)
                            <option value="{{ $k }}" @selected(old('movement_type', 'enrollment') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('movement_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6" id="batchField">
                    <label class="form-label">Destination batch <span class="text-danger req">*</span></label>
                    <select name="to_batch_id" class="form-select @error('to_batch_id') is-invalid @enderror">
                        <option value="">Select batch…</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" @selected((int) old('to_batch_id') === $b->id)>{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                    @error('to_batch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6" id="campusField">
                    <label class="form-label">Destination campus <span class="text-danger req">*</span></label>
                    <select name="to_campus_id" class="form-select @error('to_campus_id') is-invalid @enderror">
                        <option value="">Select campus…</option>
                        @foreach($campuses as $c)
                            <option value="{{ $c->id }}" @selected((int) old('to_campus_id') === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('to_campus_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6" id="statusField">
                    <label class="form-label">New student status</label>
                    <select name="to_status" class="form-select">
                        <option value="">— No change —</option>
                        @foreach($statuses as $k => $label)
                            <option value="{{ $k }}" @selected(old('to_status') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Effective date</label>
                    <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control" maxlength="255" value="{{ old('reason') }}" placeholder="e.g. Relocated to Siem Reap campus">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="alert alert-info small mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Recording a movement updates the student's current enrollment (batch/campus) and status where applicable. Deleting a log entry does not revert those changes.
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Record Movement</button>
                <a href="{{ route('student-movements.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('movement_type');
    var batchField = document.getElementById('batchField');
    var campusField = document.getElementById('campusField');
    var statusField = document.getElementById('statusField');
    var batchNeeds = ['enrollment', 'batch_change', 'transfer'];
    var statusNeeds = ['withdrawal', 'deferral', 'reinstatement', 'status_change'];

    function toggle() {
        var t = sel.value;
        var showBatch = batchNeeds.indexOf(t) !== -1;
        var showCampus = (t === 'campus_change');
        var showStatus = statusNeeds.indexOf(t) !== -1;
        batchField.style.display = showBatch ? '' : 'none';
        campusField.style.display = showCampus ? '' : 'none';
        statusField.style.display = showStatus ? '' : 'none';
        batchField.querySelector('select').required = showBatch;
        campusField.querySelector('select').required = showCampus;
    }
    sel.addEventListener('change', toggle);
    toggle();
});
</script>
@endpush
@endsection
