@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-gear text-primary me-1"></i>System Settings</h1>
    <div class="page-subtitle">Center information, notification preferences and backup settings</div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" data-loading>
            @csrf
            <div class="card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-building me-1"></i>Center Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Center Name <span class="text-danger">*</span></label>
                            <input type="text" name="center_name" class="form-control @error('center_name') is-invalid @enderror"
                                   value="{{ old('center_name', $settings['center_name']->value ?? '') }}" required>
                            @error('center_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="center_email" class="form-control @error('center_email') is-invalid @enderror"
                                   value="{{ old('center_email', $settings['center_email']->value ?? '') }}">
                            @error('center_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="center_phone" class="form-control"
                                   value="{{ old('center_phone', $settings['center_phone']->value ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="center_address" class="form-control"
                                   value="{{ old('center_address', $settings['center_address']->value ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <div class="form-text">PNG/JPG, max 2 MB. Replaces the current logo.</div>
                            @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            @php($logoPath = $settings['center_logo']->value ?? null)
                            @if($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="Logo" class="rounded border" style="max-height:70px;">
                            @else
                                <span class="text-muted small"><i class="bi bi-image me-1"></i>No logo uploaded yet</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-currency-exchange me-1"></i>Money &amp; Currency</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="riel_rate">Riel rate (៛ per US dollar)</label>
                            <input type="number" step="1" min="100" max="20000" id="riel_rate" name="riel_rate"
                                   class="form-control @error('riel_rate') is-invalid @enderror"
                                   value="{{ old('riel_rate', $settings['riel_rate']->value ?? \App\Support\Money::DEFAULT_RATE) }}">
                            @error('riel_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">
                                Invoices and payments are stored in US dollars. This rate only converts the amounts your cashiers type in
                                <span class="fw-semibold">៛</span>, so 1&nbsp;<span class="fw-semibold">$</span> = <span id="ratePreview">4,100</span>&nbsp;<span class="fw-semibold">៛</span>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-bell me-1"></i>Notification Preferences</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">In-system notifications only. No Telegram / Email / SMS / WhatsApp messages are sent automatically unless the corresponding service is configured and connected.</p>
                    @foreach([
                        'notify_followup' => 'Upcoming follow-up reminders',
                        'notify_overdue' => 'Overdue task alerts',
                        'notify_events' => 'Upcoming alumni event notices',
                        'notify_opportunities' => 'New job / opportunity announcements',
                    ] as $key => $label)
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $key }}" name="{{ $key }}" value="1"
                                   {{ old($key, ($settings[$key]->value ?? '1') === '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold"><i class="bi bi-hdd me-1"></i>Backup Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Backup Frequency</label>
                            <select name="backup_frequency" class="form-select">
                                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $v => $l)
                                    <option value="{{ $v }}" {{ old('backup_frequency', $settings['backup_frequency']->value ?? 'weekly') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Records the intended schedule. Use the download button to create a backup manually.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Retention (days)</label>
                            <input type="number" name="backup_retention" class="form-control" min="1" max="365"
                                   value="{{ old('backup_retention', $settings['backup_retention']->value ?? 30) }}">
                        </div>
                    </div>
                    @if(auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('backup.download') }}" class="btn btn-outline-primary btn-sm mt-3">
                            <i class="bi bi-download me-1"></i>Download Database Backup (.sql)
                        </a>
                    @endif
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-people me-1"></i>Roles Overview</div>
            <div class="list-group list-group-flush">
                @foreach($roles as $role)
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $role->display_name }}</div>
                            <div class="text-muted small">{{ $role->description }}</div>
                        </div>
                        <span class="badge text-bg-primary rounded-pill">{{ $role->users_count }} user{{ $role->users_count === 1 ? '' : 's' }}</span>
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-transparent">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-person-gear me-1"></i>Manage Staff Accounts</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Quick Links</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('campuses.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-geo-alt me-2 text-primary"></i>Campuses / Branches</a>
                <a href="{{ route('courses.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-book me-2 text-primary"></i>Courses</a>
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-person-badge me-2 text-primary"></i>Staff Accounts</a>
                @if(auth()->user()->hasRole('super_admin'))
                    <a href="{{ route('audit-logs.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-shield-check me-2 text-primary"></i>Audit Logs</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('riel_rate');
    var preview = document.getElementById('ratePreview');
    if (!input || !preview) { return; }
    function show() {
        var rate = parseInt(input.value, 10);
        preview.textContent = rate > 0 ? rate.toLocaleString('en-US') : '—';
    }
    input.addEventListener('input', show);
    show();
})();
</script>
@endpush
