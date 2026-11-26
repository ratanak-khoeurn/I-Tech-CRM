@extends('layouts.app')

@section('title', $opportunity->exists ? 'Edit Opportunity' : 'Add Opportunity')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-briefcase text-primary me-1"></i>
                {{ $opportunity->exists ? 'Edit Opportunity — ' . $opportunity->title : 'Add Opportunity' }}
            </div>
            <div class="card-body">
                <form method="POST" data-loading
                      action="{{ $opportunity->exists ? route('opportunities.update', $opportunity) : route('opportunities.store') }}">
                    @csrf
                    @if($opportunity->exists) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Opportunity Title <span class="required-mark">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $opportunity->title) }}" required maxlength="255">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Opportunity Type <span class="required-mark">*</span></label>
                            <select name="opportunity_type" class="form-select" required>
                                <option value="">— Select —</option>
                                @foreach(\App\Models\Opportunity::TYPES as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('opportunity_type', $opportunity->opportunity_type) === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Organization</label>
                            <input type="text" name="organization" class="form-control" value="{{ old('organization', $opportunity->organization) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Application Deadline</label>
                            <input type="date" name="application_deadline" class="form-control" value="{{ old('application_deadline', $opportunity->application_deadline?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $opportunity->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Requirements</label>
                            <textarea name="requirements" class="form-control" rows="2" placeholder="Skills, qualifications, keywords used for alumni matching...">{{ old('requirements', $opportunity->requirements) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Information</label>
                            <input type="text" name="contact_info" class="form-control" value="{{ old('contact_info', $opportunity->contact_info) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Application URL</label>
                            <input type="url" name="application_url" class="form-control @error('application_url') is-invalid @enderror" value="{{ old('application_url', $opportunity->application_url) }}" placeholder="https://...">
                            @error('application_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Target Course Code</label>
                            <input type="text" name="target_course" class="form-control" value="{{ old('target_course', $opportunity->target_course) }}" placeholder="e.g. WEB">
                            <div class="form-text">Leave empty for any course</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Target Graduation Years</label>
                            <input type="text" name="target_graduation_year" class="form-control" value="{{ old('target_graduation_year', $opportunity->target_graduation_year) }}" placeholder="e.g. 2022,2023">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Publication Status <span class="required-mark">*</span></label>
                            <select name="publication_status" class="form-select" required>
                                @foreach(['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('publication_status', $opportunity->publication_status) === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ $opportunity->exists ? route('opportunities.show', $opportunity) : route('opportunities.index') }}" class="btn btn-light">Cancel</a>
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>{{ $opportunity->exists ? 'Update' : 'Create' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
