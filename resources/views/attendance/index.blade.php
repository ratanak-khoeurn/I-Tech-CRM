@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
@php
    $isFace = ($mode ?? 'face') === 'face';
    $selectedCampus = \App\Support\CampusContext::filterFor(request());
@endphp
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">
            <i class="bi bi-{{ $isFace ? 'camera-video' : 'fingerprint' }} text-primary me-1"></i>{{ $isFace ? 'Face &amp; ID Attendance Scan' : 'Fingerprint / Device Attendance' }}
        </h1>
        <div class="page-subtitle">{{ $isFace ? 'Use the camera or scan an existing student or employee ID to record check-in and check-out.' : 'Import attendance punches exported from a fingerprint terminal to record check-in and check-out.' }}</div>
    </div>
</div>

@if($scan = session('attendance_scan'))
    @php
        $scanAlert = match ($scan['action']) {
            'checked_in' => 'success',
            'checked_out' => 'info',
            default => 'secondary',
        };
    @endphp
    <div class="alert alert-{{ $scanAlert }} alert-dismissible fade show d-flex align-items-center gap-2" role="status">
        <i class="bi bi-{{ $scan['action'] === 'checked_in' ? 'box-arrow-in-right' : ($scan['action'] === 'checked_out' ? 'box-arrow-right' : 'check-circle') }} fs-5"></i>
        <span>{{ $scan['message'] }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->has('code') || $errors->has('person_type'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first('code') ?: $errors->first('person_type') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($import = session('device_import'))
    @php
        $importAlert = $import['not_found'] || $import['ambiguous'] || $import['invalid'] ? 'warning' : 'success';
    @endphp
    <div class="alert alert-{{ $importAlert }} alert-dismissible fade show" role="alert">
        <div class="fw-semibold mb-1"><i class="bi bi-fingerprint me-1"></i>Device import finished — {{ $import['total'] }} row(s) processed.</div>
        <ul class="mb-0 small">
            <li>Checked in: {{ $import['checked_in'] }}</li>
            <li>Checked out: {{ $import['checked_out'] }}</li>
            <li>Already complete: {{ $import['complete'] }}</li>
            @if($import['not_found'])<li>No matching person: {{ $import['not_found'] }}</li>@endif
            @if($import['ambiguous'])<li>Ambiguous ID: {{ $import['ambiguous'] }}</li>@endif
            @if($import['invalid'])<li>Invalid date/time: {{ $import['invalid'] }}</li>@endif
        </ul>
        @if(!empty($import['issues']))
            <details class="small mt-2"><summary>{{ count($import['issues']) }} row issue(s) — view details</summary>
                <ul class="mt-1 mb-0">@foreach(array_slice($import['issues'], 0, 200) as $issue)<li>{{ $issue }}</li>@endforeach</ul>
            </details>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@once
    @if(session('export_empty'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('export_empty') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endonce

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="rounded-3 bg-primary-subtle text-primary p-3"><i class="bi bi-people fs-4"></i></span>
            <div><div class="text-muted small">Scanned today</div><div class="fs-4 fw-bold">{{ $date === now()->toDateString() ? $checkInsCount : '—' }}</div></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="rounded-3 bg-success-subtle text-success p-3"><i class="bi bi-box-arrow-in-right fs-4"></i></span>
            <div><div class="text-muted small">Currently checked in</div><div class="fs-4 fw-bold">{{ $date === now()->toDateString() ? $checkInsCount - $checkedOutCount : '—' }}</div></div>
        </div></div>
    </div>
</div>

@if($isFace)
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0 fw-bold"><i class="bi bi-camera-video text-primary me-1"></i>Face scan attendance</h2></div>
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-lg-7">
                <div class="ratio ratio-16x9 bg-dark rounded overflow-hidden">
                    <video id="attendanceCamera" class="w-100 h-100" style="object-fit: cover" autoplay muted playsinline aria-label="Camera preview"></video>
                </div>
            </div>
            <div class="col-lg-5">
                <p class="fw-semibold mb-1">Scan a registered student or employee</p>
                <p class="text-muted small">Center one face in the camera. Face scanning requires an enrolled student or employee profile with consent. You can also record attendance with the ID scanner below.</p>
                <div id="cameraStatus" class="small text-muted mb-3" role="status" aria-live="polite">Camera is off.</div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary" id="startCameraButton"><i class="bi bi-camera-video me-1"></i>Start camera</button>
                    <button type="button" class="btn btn-primary" id="scanFaceButton" disabled><i class="bi bi-person-bounding-box me-1"></i>Scan face</button>
                    <button type="button" class="btn btn-outline-secondary" id="stopCameraButton" disabled>Stop camera</button>
                </div>
                <div class="form-text mt-2">Allow camera access when prompted. On other devices, this page must use HTTPS.</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0 fw-bold"><i class="bi bi-upc-scan text-primary me-1"></i>Scan ID manually</h2></div>
    <div class="card-body">
        <form method="POST" action="{{ route('attendance.scan') }}" id="attendanceScanForm" class="row g-3 align-items-end" autocomplete="off">
            @csrf
            <div class="col-md-3">
                <label for="person_type" class="form-label">ID type</label>
                <select id="person_type" name="person_type" class="form-select">
                    <option value="auto" @selected(old('person_type', 'auto') === 'auto')>Detect automatically</option>
                    <option value="student" @selected(old('person_type') === 'student')>Student</option>
                    <option value="employee" @selected(old('person_type') === 'employee')>Employee</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="campus_id" class="form-label">Attendance campus</label>
                <select id="campus_id" name="campus_id" class="form-select">
                    <option value="">Use person’s assigned campus</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected((string) old('campus_id') === (string) $campus->id)>{{ $campus->name }}{{ $campus->code ? ' (' . $campus->code . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="attendance_code" class="form-label">Scan or enter ID</label>
                <input type="text" id="attendance_code" name="code" class="form-control form-control-lg @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Scan student or employee ID" required maxlength="60" autofocus>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i>Record Scan</button>
            </div>
        </form>
        <div class="form-text mt-2">First scan records check-in; the next scan records check-out. Further scans won’t change a completed record for today.</div>
    </div>
</div>
@endif

@if(!$isFace)
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0 fw-bold"><i class="bi bi-fingerprint text-primary me-1"></i>Fingerprint / device punch import</h2></div>
    <div class="card-body">
        <p class="text-muted small mb-3">Import attendance logs exported from a standalone fingerprint terminal (ZKTeco, Futronic, DigitalPersona, etc.). The device does the enrollment and matching; here we only convert its punch rows into check-in / check-out records. Each person's device <strong>User ID / PIN must equal their CRM code</strong> (student_code or employee_code), or add a <code>person_type</code> column to disambiguate.</p>
        <form method="POST" action="{{ route('attendance.device-import') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6">
                <label for="device_file" class="form-label">Punch log CSV</label>
                <input type="file" id="device_file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt" required>
                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import punches</button>
            </div>
            <div class="col-md-3 d-grid">
                <a href="{{ route('attendance.device-template') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Sample template</a>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><h2 class="h6 mb-0 fw-bold">Attendance Records</h2><small class="text-muted">{{ \Illuminate\Support\Carbon::parse($date)->format('l, d M Y') }}</small></div>
        <form method="GET" action="{{ route($isFace ? 'attendance.face' : 'attendance.finger') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="date" class="form-label small mb-1">Date</label>
                <input type="date" id="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
            </div>
            <div class="col-auto">
                <label for="filter_type" class="form-label small mb-1">Person</label>
                <select id="filter_type" name="person_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="student" @selected(request('person_type') === 'student')>Students</option>
                    <option value="employee" @selected(request('person_type') === 'employee')>Employees</option>
                </select>
            </div>
            <div class="col-auto">
                <label for="filter_campus" class="form-label small mb-1">Campus</label>
                <select id="filter_campus" name="campus_id" class="form-select form-select-sm">
                    <option value="">All campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected($selectedCampus === (int) $campus->id)>{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label for="attendance_q" class="form-label small mb-1">Search ID/name</label>
                <input type="search" id="attendance_q" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Name or ID">
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button></div>
        </form>
    </div>
    <div class="border-top px-3 py-2 bg-body-tertiary d-flex flex-wrap align-items-end gap-2">
        <form method="GET" action="{{ route('attendance.export') }}" class="d-flex flex-wrap align-items-end gap-2">
            <div>
                <label class="form-label small mb-1" for="export_from">Export period</label>
                <input type="date" id="export_from" name="from" class="form-control form-control-sm" value="{{ request('from', $date) }}">
            </div>
            <div>
                <label class="form-label small mb-1" for="export_to">to</label>
                <input type="date" id="export_to" name="to" class="form-control form-control-sm" value="{{ request('to', $date) }}">
            </div>
            <div>
                <label class="form-label small mb-1" for="export_type">Person</label>
                <select id="export_type" name="person_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="student" @selected(request('person_type') === 'student')>Students</option>
                    <option value="employee" @selected(request('person_type') === 'employee')>Employees</option>
                </select>
            </div>
            <div>
                <label class="form-label small mb-1" for="export_campus">Campus</label>
                <select id="export_campus" name="campus_id" class="form-select form-select-sm">
                    <option value="">All campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected($selectedCampus === (int) $campus->id)>{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><button class="btn btn-sm btn-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV</button></div>
        </form>
        <small class="text-muted align-self-center">Exports every day from “Export period” to “to”. Set both to the same day for a single-day export.</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Person</th><th>Type</th><th>Campus</th><th>Check-in</th><th>Check-out</th><th>Recorded by</th><th>Remark</th></tr></thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $record->personName() }}</div>
                            <div class="text-muted small">{{ $record->personCode() ?? 'ID unavailable' }}</div>
                        </td>
                        <td><span class="badge text-bg-{{ $record->student_id ? 'primary' : 'info' }}">{{ $record->personTypeLabel() }}</span></td>
                        <td>{{ $record->campus?->name ?? '—' }}</td>
                        <td>{{ $record->check_in_at?->format('g:i:s A') ?? '—' }}<div class="small text-muted">{{ $record->checkedInBy?->name ?? '—' }}</div></td>
                        <td>
                            @if($record->check_out_at)
                                {{ $record->check_out_at->format('g:i:s A') }}<div class="small text-muted">{{ $record->checkedOutBy?->name ?? '—' }}</div>
                            @else
                                <span class="badge text-bg-warning">Still checked in</span>
                            @endif
                        </td>
                        <td>{{ $record->checkedInBy?->name ?? '—' }}</td>
                        <td>
                            @if($record->remark)
                                <span class="badge text-bg-danger">{{ $record->remark }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state py-5"><i class="bi bi-calendar-check"></i><p class="mb-0">No attendance records for this date and filter.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())<div class="card-footer bg-transparent">{{ $records->links() }}</div>@endif
</div>

@if($isFace)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeInput = document.getElementById('attendance_code');
    const scanForm = document.getElementById('attendanceScanForm');
    const video = document.getElementById('attendanceCamera');
    const startButton = document.getElementById('startCameraButton');
    const scanButton = document.getElementById('scanFaceButton');
    const stopButton = document.getElementById('stopCameraButton');
    const cameraStatus = document.getElementById('cameraStatus');
    let cameraStream = null;

    codeInput?.focus();
    codeInput?.select();

    function stopCamera() {
        cameraStream?.getTracks().forEach(track => track.stop());
        cameraStream = null;
        video.srcObject = null;
        startButton.disabled = false;
        scanButton.disabled = true;
        stopButton.disabled = true;
        cameraStatus.textContent = 'Camera is off.';
    }

    startButton?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia) {
            cameraStatus.textContent = 'Camera access is not available. Open this page on localhost or over HTTPS.';
            return;
        }

        startButton.disabled = true;
        cameraStatus.textContent = 'Requesting camera access…';

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false,
            });
            video.srcObject = cameraStream;
            await video.play();
            scanButton.disabled = false;
            stopButton.disabled = false;
            cameraStatus.textContent = 'Camera is ready. Center one face and select Scan face.';
        } catch (error) {
            stopCamera();
            cameraStatus.textContent = error.name === 'NotAllowedError'
                ? 'Camera permission was denied. Allow camera access in your browser and try again.'
                : 'Could not start the camera. Check that it is connected and not being used by another app.';
        }
    });

    stopButton?.addEventListener('click', stopCamera);

    scanButton?.addEventListener('click', async function () {
        if (!cameraStream || !video.videoWidth || !video.videoHeight) {
            cameraStatus.textContent = 'Wait for the camera preview, then try again.';
            return;
        }

        scanButton.disabled = true;
        cameraStatus.textContent = 'Scanning face…';

        try {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            const image = await new Promise((resolve, reject) => {
                canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Could not capture an image.')), 'image/jpeg', 0.9);
            });
            const formData = new FormData();
            formData.append('image', image, 'attendance-scan.jpg');

            const response = await fetch(@json(route('face.identify')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Face was not recognized. Try again with one face clearly visible.');
            }

            document.getElementById('person_type').value = result.person_type;
            codeInput.value = result.code;
            cameraStatus.textContent = `Recognized ${result.name}. Recording attendance…`;
            stopCamera();
            scanForm.requestSubmit();
        } catch (error) {
            cameraStatus.textContent = error.message || 'Face scan failed. Please try again.';
            scanButton.disabled = !cameraStream;
        }
    });

    window.addEventListener('beforeunload', stopCamera);

    @if(session('attendance_scan'))
        if (codeInput) {
            codeInput.value = '';
            codeInput.focus();
        }
    @endif
});
</script>
@endpush
@endif
@endsection
