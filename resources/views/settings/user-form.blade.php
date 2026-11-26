@extends('layouts.app')

@section('title', $user->exists ? 'Edit Staff Account' : 'Add Staff Account')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-person-gear text-primary me-1"></i>{{ $user->exists ? 'Edit Staff Account' : 'Add Staff Account' }}</h1>
    <div class="page-subtitle">{{ $user->exists ? $user->name . ' — ' . $user->email : 'Create a new system user and assign a role' }}</div>
</div>

<div class="card mx-auto" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" enctype="multipart/form-data" data-loading>
            @csrf
            @if($user->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Profile Photo</label>
                <div class="d-flex align-items-center gap-3">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle border" width="56" height="56" style="object-fit: cover;">
                    @else
                        <span class="avatar-circle d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">{{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}</span>
                    @endif
                    <div class="flex-grow-1">
                        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">JPG/PNG/WEBP, max 2 MB. Leave empty to keep the current photo.</div>
                        @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required
                        @if($user->id === auth()->id()) disabled @endif>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (int) old('role_id', $user->role_id) === $role->id ? 'selected' : '' }}>
                            {{ $role->display_name }} — {{ $role->description }}
                        </option>
                    @endforeach
                </select>
                @if($user->id === auth()->id())
                    <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                    <div class="form-text">You cannot change your own role.</div>
                @endif
                @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @php
    $selectedCampusIds = collect(old('campus_ids', $user->campuses->pluck('id')->all()))
        ->map(fn ($id) => (int) $id)->all();
    $campusBound = in_array((int) old('role_id', $user->role_id), array_map('intval', $campusBoundRoleIds), true);
@endphp

<div class="mb-3" id="campusAssignment" @if(!$campusBound) style="display:none" @endif>
    <label class="form-label">Assigned Campuses</label>
    @if($campuses->isEmpty())
        <div class="form-text text-danger"><i class="bi bi-exclamation-circle me-1"></i>No campuses exist yet. Create one under Campuses first.</div>
    @else
        <div class="border rounded p-2" style="max-height: 190px; overflow-y: auto;">
            @foreach($campuses as $campus)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="campus_ids[]" id="campus{{ $campus->id }}"
                           value="{{ $campus->id }}" {{ in_array((int) $campus->id, $selectedCampusIds, true) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="campus{{ $campus->id }}">
                        {{ $campus->name }}
                        @if($campus->code)<span class="text-muted">({{ $campus->code }})</span>@endif
                        @if(!$campus->is_active)<span class="badge text-bg-secondary ms-1">Inactive</span>@endif
                    </label>
                </div>
            @endforeach
        </div>
        <div class="form-text">
            @if (\App\Support\CampusContext::visibleIds() === null)
                Tick the campuses this account may work in. An account with none ticked
                sees no campus data at all — only a Super Admin works across every campus.
            @else
                You can only assign campuses from your own list below.
            @endif
        </div>
        @error('campus_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('campus_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @endif
</div>

<div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password @if(!$user->exists)<span class="text-danger">*</span>@endif</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           {{ $user->exists ? '' : 'required' }} autocomplete="new-password">
                    @if($user->exists)<div class="form-text">Leave blank to keep the current password. Min 8 characters.</div>@endif
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"
                           {{ $user->exists ? '' : 'required' }}>
                </div>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                       @if($user->id === auth()->id()) disabled @endif>
                <label class="form-check-label" for="is_active">Account active</label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                    <div class="form-text">You cannot deactivate your own account.</div>
                @endif
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ $user->exists ? 'Update Account' : 'Create Account' }}</button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const roleSelect = document.querySelector('select[name="role_id"]');
        const campusBlock = document.getElementById('campusAssignment');
        const campusBoundRoleIds = @json(array_map('intval', $campusBoundRoleIds));
        if (!roleSelect || !campusBlock) return;
        const sync = () => {
            campusBlock.style.display = campusBoundRoleIds.includes(parseInt(roleSelect.value, 10)) ? '' : 'none';
        };
        roleSelect.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
