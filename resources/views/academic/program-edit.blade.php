@extends('layouts.app')

@section('title', $program->exists ? 'Edit Program' : 'Add Program')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-mortarboard text-primary me-1"></i>{{ $program->exists ? 'Edit Program' : 'Add Program' }}</h1>
    <div class="page-subtitle">{{ $program->exists ? $program->name : 'Create an academic program to group courses.' }}</div>
</div>

@include('academic._tabs')

<div class="card mx-auto" style="max-width: 1000px;">
    <div class="card-body">
        <form method="POST" action="{{ $program->exists ? route('programs.update', $program) : route('programs.store') }}" data-loading>
            @csrf
            @if($program->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Program name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $program->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $program->code) }}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $program->description) }}</textarea>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $program->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ $program->exists ? 'Update Program' : 'Create Program' }}</button>
                <a href="{{ route('programs.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
