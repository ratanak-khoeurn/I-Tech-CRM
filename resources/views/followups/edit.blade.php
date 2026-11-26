@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil text-primary me-1"></i>Edit Follow-up Task</div>
            <div class="card-body">
                <form method="POST" action="{{ route('follow-ups.update', $task) }}" data-loading>
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Student <span class="required-mark">*</span></label>
                        <select name="student_id" class="form-select" required>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected($task->student_id === $s->id)>{{ $s->name_english }} ({{ $s->student_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Title <span class="required-mark">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $task->description) }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('priority', $task->priority) === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date <span class="required-mark">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $task->due_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}" @selected(old('assigned_to', $task->assigned_to) == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reminder At</label>
                            <input type="datetime-local" name="reminder_at" class="form-control" value="{{ old('reminder_at', $task->reminder_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('follow-ups.index') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
