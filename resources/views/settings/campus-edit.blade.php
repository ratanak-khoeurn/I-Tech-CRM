@extends('layouts.app')

@section('title', 'Edit Campus')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-mortarboard text-primary me-1"></i>Edit Campus</h1>
        <div class="page-subtitle">{{ $campus->name }}</div>
    </div>
</div>

@include('academic._tabs')

<div class="card mx-auto">
    <div class="card-body">
        <form method="POST" action="{{ route('campuses.update', $campus) }}" enctype="multipart/form-data" data-loading>
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $campus->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $campus->code) }}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $campus->address) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $campus->phone) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Campus Photo</label>
                <div class="d-flex align-items-center gap-3">
                    @if($campus->image)
                        <img src="{{ asset('storage/' . $campus->image) }}" alt="{{ $campus->name }}" class="rounded border" width="72" height="54" style="object-fit: cover;">
                    @else
                        <span class="rounded border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width:72px;height:54px;"><i class="bi bi-image"></i></span>
                    @endif
                    <div class="flex-grow-1">
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">JPG/PNG/WEBP, max 2 MB. Leave empty to keep the current photo.</div>
                        @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $campus->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Campus</button>
                <a href="{{ route('campuses.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
