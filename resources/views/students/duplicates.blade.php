@extends('layouts.app')

@section('title', 'Duplicate Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title"><i class="bi bi-files text-warning me-1"></i>Duplicate Student Detection</h1>
        <div class="page-subtitle">Groups detected by matching email or phone number</div>
    </div>
    <a href="{{ route('students.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Students</a>
</div>

@if($groups->isEmpty())
    <div class="card"><div class="card-body">
        <div class="empty-state">
            <i class="bi bi-check2-circle text-success"></i>
            <p class="mb-0 fw-semibold">No duplicate records found</p>
            <p class="small">All student emails and phone numbers are unique.</p>
        </div>
    </div></div>
@else
    @foreach($groups as $group)
        <div class="card mb-3">
            <div class="card-header bg-warning bg-opacity-10">
                <i class="bi bi-exclamation-triangle text-warning me-1"></i>Duplicate group — {{ $group['type'] }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('students.merge') }}" onsubmit="return confirm('You are about to merge these records. The duplicate will be deleted and its data moved to the record you keep. This action is difficult to reverse. Would you like to proceed?')">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th style="width:80px">Action</th><th>Student</th><th>Phone</th><th>Email</th><th>Created</th><th>Records</th></tr></thead>
                            <tbody>
                            @foreach($group['students'] as $i => $s)
                                <tr>
                                    <td>
                                        @if($i === 0)
                                            <input type="radio" name="keep_id" value="{{ $s->id }}" checked class="form-check-input" title="Keep this record">
                                            <span class="badge bg-success ms-1">KEEP</span>
                                        @else
                                            <input type="radio" name="keep_id" value="{{ $s->id }}" class="form-check-input keep-radio">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('students.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->name_english }}</a>
                                        <div class="text-muted" style="font-size:.75rem">{{ $s->student_code }}</div>
                                    </td>
                                    <td class="small">{{ $s->phone }}</td>
                                    <td class="small">{{ $s->email ?: '—' }}</td>
                                    <td class="small">{{ $s->created_at->format('M d, Y') }}</td>
                                    <td class="small">
                                        {{ $s->communicationLogs()->count() }} comms,
                                        {{ $s->followUpTasks()->count() }} tasks,
                                        {{ $s->documents()->count() }} docs
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($group['students']->count() === 2)
                        <input type="hidden" name="discard_id" value="{{ $group['students'][1]->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Select which record to KEEP. The other record's communications, tasks, documents and notes will be moved to the kept record, empty fields will be filled, then the duplicate is deleted.</small>
                            <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-merge me-1"></i>Merge Records</button>
                        </div>
                    @else
                        <small class="text-muted">More than 2 duplicates in this group — merge them two at a time. Choose the record to keep and one to discard:</small>
                        <div class="row g-2 mt-1 align-items-end">
                            <div class="col-auto">
                                <label class="form-label mb-0 small">Discard record</label>
                                <select name="discard_id" class="form-select form-select-sm">
                                    @foreach($group['students'] as $s)
                                        <option value="{{ $s->id }}">{{ $s->name_english }} ({{ $s->student_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-merge me-1"></i>Merge Selected Pair</button>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    @endforeach
@endif
@endsection

@push('scripts')
<script>
// Keep the discard_id in sync when the KEEP radio changes (2-record groups)
document.querySelectorAll('.keep-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        const form = radio.closest('form');
        const discard = form.querySelector('input[name="discard_id"]');
        if (!discard) return;
        const keep = form.querySelector('input[name="keep_id"]:checked').value;
        const all = [...form.querySelectorAll('input.keep-radio, input[name="keep_id"][type=radio]')];
        const other = all.find(r => r.value !== keep);
        if (other) discard.value = other.value;
    });
});
</script>
@endpush
