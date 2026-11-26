@extends('layouts.app')

@section('title', 'Edit Fee')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-sliders text-primary me-1"></i>Edit Fee</h1>
    <div class="page-subtitle">{{ $feeStructure->name }}</div>
</div>

<div class="card mx-auto" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="{{ route('fee-structures.update', $feeStructure) }}" data-loading>
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Fee name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $feeStructure->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Course <span class="text-danger">*</span></label>
                    <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" @selected((int) old('course_id', $feeStructure->course_id) === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Campus</label>
                    <select name="campus_id" class="form-select">
                        <option value="">All campuses</option>
                        @foreach($campuses as $c)
                            <option value="{{ $c->id }}" @selected((int) old('campus_id', $feeStructure->campus_id) === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Fee type <span class="text-danger">*</span></label>
                    <select name="fee_type" class="form-select" required>
                        @foreach(\App\Models\FeeStructure::FEE_TYPES as $k => $label)
                            <option value="{{ $k }}" @selected(old('fee_type', $feeStructure->fee_type) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Amount ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $feeStructure->amount) }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Frequency <span class="text-danger">*</span></label>
                    <select name="frequency" class="form-select" required>
                        @foreach(\App\Models\FeeStructure::FREQUENCIES as $k => $label)
                            <option value="{{ $k }}" @selected(old('frequency', $feeStructure->frequency) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $feeStructure->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Fee</button>
                <a href="{{ route('fee-structures.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
