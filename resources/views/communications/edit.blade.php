@extends('layouts.app')

@section('title', 'Edit Communication')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil text-primary me-1"></i>Edit Communication Record</div>
            <div class="card-body">
                <form method="POST" action="{{ route('communications.update', $log) }}" enctype="multipart/form-data" data-loading>
                    @csrf @method('PUT')
                    <input type="hidden" name="student_id" value="{{ $log->student_id }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <input type="text" class="form-control" value="{{ $log->student?->name_english }} ({{ $log->student?->student_code }})" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Channel <span class="required-mark">*</span></label>
                            <select name="channel" class="form-select" required>
                                @foreach(\App\Models\CommunicationLog::CHANNELS as $k => $lbl)
                                    <option value="{{ $k }}" @selected($log->channel === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Date & Time <span class="required-mark">*</span></label>
                            <input type="datetime-local" name="contact_datetime" class="form-control" value="{{ old('contact_datetime', $log->contact_datetime->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Direction <span class="required-mark">*</span></label>
                            <select name="direction" class="form-select" required>
                                <option value="outgoing" @selected($log->direction === 'outgoing')>Outgoing</option>
                                <option value="incoming" @selected($log->direction === 'incoming')>Incoming</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Subject <span class="required-mark">*</span></label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject', $log->subject) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message Summary</label>
                            <textarea name="message_summary" class="form-control" rows="3">{{ old('message_summary', $log->message_summary) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outcome</label>
                            <input type="text" name="outcome" class="form-control" value="{{ old('outcome', $log->outcome) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="followup_required" value="1" class="form-check-input" id="fuReq" @checked(old('followup_required', $log->followup_required))>
                                <label class="form-check-label" for="fuReq">Follow-up required</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" name="next_followup_date" class="form-control" value="{{ old('next_followup_date', $log->next_followup_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Replace Attachment</label>
                            <input type="file" name="attachment" class="form-control">
                            @if($log->attachment)<div class="form-text">Current: {{ basename($log->attachment) }}</div>@endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Internal Notes</label>
                            <input type="text" name="internal_notes" class="form-control" value="{{ old('internal_notes', $log->internal_notes) }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('communications.index') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
