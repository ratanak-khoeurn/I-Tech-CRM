@extends('layouts.app')

@section('title', $employee->fullName())

@section('content')
@php $employeeFace = $employee->faceProfile; @endphp
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-person-vcard text-primary me-1"></i>{{ $employee->fullName() }}</h1>
        <div class="page-subtitle">
            <span class="badge text-bg-secondary">{{ $employee->employee_code }}</span>
            {{ $employee->position->title ?? 'No position' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#employeeFaceModal"><i class="bi bi-person-bounding-box me-1"></i>{{ $employeeFace?->is_active ? 'Update face profile' : 'Enroll face' }}</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#employeeCardModal" data-card-url="{{ route('employees.card', ['employee' => $employee, 'preview' => 1]) }}" data-employee-name="{{ $employee->fullName() }}"><i class="bi bi-person-vcard me-1"></i>ID Card</button>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('employees.index') }}" class="btn btn-light">Back</a>
    </div>
</div>

@include('hr._tabs')

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="" class="rounded-circle mb-2" width="120" height="120" style="object-fit:cover">
                @else
                    <div class="avatar-circle mx-auto mb-2" style="width:120px;height:120px;font-size:2.6rem">{{ strtoupper(substr($employee->first_name, 0, 1)) }}</div>
                @endif
                <h5 class="mb-0">{{ $employee->fullName() }}</h5>
                <div class="text-muted">{{ $employee->position->title ?? '—' }}</div>
                @php $tb = ['active'=>'success','on_leave'=>'warning','resigned'=>'secondary','terminated'=>'danger'][$employee->status] ?? 'secondary'; @endphp
                <span class="badge text-bg-{{ $tb }} mt-2">{{ $employee->statusLabel() }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Employee code</dt><dd class="col-sm-8">{{ $employee->employee_code }}</dd>
                    <dt class="col-sm-4">Department</dt><dd class="col-sm-8">{{ $employee->position->department ?? '—' }}</dd>
                    <dt class="col-sm-4">Campus</dt><dd class="col-sm-8">{{ $employee->campus->name ?? '—' }}</dd>
                    <dt class="col-sm-4">Employment type</dt><dd class="col-sm-8">{{ $employee->employmentTypeLabel() }}</dd>
                    <dt class="col-sm-4">Hire date</dt><dd class="col-sm-8">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Contract end</dt><dd class="col-sm-8">{{ $employee->contract_end_date?->format('M d, Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $employee->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $employee->phone ?? '—' }}</dd>
                    <dt class="col-sm-4">Gender</dt><dd class="col-sm-8">{{ ucfirst($employee->gender ?? '—') }}</dd>
                    <dt class="col-sm-4">Date of birth</dt><dd class="col-sm-8">{{ $employee->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $employee->address ?? '—' }}</dd>
                    <dt class="col-sm-4">Linked login</dt><dd class="col-sm-8">{{ $employee->user ? $employee->user->email : '—' }}</dd>
                    @if($canViewSensitive)
                        <dt class="col-sm-4">Salary <i class="bi bi-lock text-muted"></i></dt>
                        <dd class="col-sm-8">{{ $employee->salary !== null ? '$' . number_format((float) $employee->salary, 2) : '—' }}</dd>
                    @endif
                    @if($employee->notes)
                        <dt class="col-sm-4">Notes</dt><dd class="col-sm-8">{{ $employee->notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@include('hr._card-preview-modal')

<!-- Employee Face Enrollment Modal -->
<div class="modal fade" id="employeeFaceModal" tabindex="-1" aria-labelledby="employeeFaceTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="employeeFaceForm">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeFaceTitle"><i class="bi bi-person-bounding-box me-1"></i>Enroll face — {{ $employee->fullName() }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">A face embedding is stored for attendance matching; the captured photo is not retained. Obtain the employee's consent before proceeding.</p>
                <div class="ratio ratio-16x9 bg-dark rounded overflow-hidden mb-3">
                    <video id="employeeFaceCamera" class="w-100 h-100" style="object-fit: cover" autoplay muted playsinline aria-label="Face enrollment camera preview"></video>
                </div>
                <div id="employeeFaceStatus" class="small text-muted mb-3" role="status" aria-live="polite">Start the camera and center one face in view.</div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="consent_confirmed" value="1" id="employeeFaceConsent" required>
                    <label class="form-check-label" for="employeeFaceConsent">I confirm the employee has consented to face-based attendance processing.</label>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="startEmployeeFaceCamera"><i class="bi bi-camera-video me-1"></i>Start camera</button>
                    <button type="button" class="btn btn-outline-secondary" id="stopEmployeeFaceCamera" disabled>Stop camera</button>
                </div>
                <button type="submit" class="btn btn-primary" id="saveEmployeeFace" disabled><i class="bi bi-check2-circle me-1"></i>Capture and enroll</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const faceForm = document.getElementById('employeeFaceForm');
    const faceVideo = document.getElementById('employeeFaceCamera');
    const faceStatus = document.getElementById('employeeFaceStatus');
    const startButton = document.getElementById('startEmployeeFaceCamera');
    const stopButton = document.getElementById('stopEmployeeFaceCamera');
    const saveButton = document.getElementById('saveEmployeeFace');
    let stream = null;

    function stopCamera() {
        stream?.getTracks().forEach(track => track.stop());
        stream = null;
        faceVideo.srcObject = null;
        startButton.disabled = false;
        stopButton.disabled = true;
        saveButton.disabled = true;
    }

    startButton?.addEventListener('click', async function () {
        if (!navigator.mediaDevices?.getUserMedia) {
            faceStatus.textContent = 'Camera access is unavailable. Use localhost or open this page over HTTPS.';
            return;
        }
        startButton.disabled = true;
        faceStatus.textContent = 'Requesting camera access…';
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
            faceVideo.srcObject = stream;
            await faceVideo.play();
            stopButton.disabled = false;
            saveButton.disabled = false;
            faceStatus.textContent = 'Camera ready. Center only the employee’s face, confirm consent, and select Capture and enroll.';
        } catch (error) {
            stopCamera();
            faceStatus.textContent = error.name === 'NotAllowedError'
                ? 'Camera permission was denied. Allow camera access and try again.'
                : 'Could not start the camera. Check that it is connected and not being used by another app.';
        }
    });

    stopButton?.addEventListener('click', stopCamera);
    document.getElementById('employeeFaceModal')?.addEventListener('hidden.bs.modal', stopCamera);

    faceForm?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!stream || !faceVideo.videoWidth || !faceVideo.videoHeight) {
            faceStatus.textContent = 'Start the camera and wait for the preview before enrolling.';
            return;
        }
        if (!document.getElementById('employeeFaceConsent').checked) {
            faceStatus.textContent = 'Confirm consent before enrolling this face.';
            return;
        }

        saveButton.disabled = true;
        faceStatus.textContent = 'Capturing and processing face…';

        try {
            const canvas = document.createElement('canvas');
            canvas.width = faceVideo.videoWidth;
            canvas.height = faceVideo.videoHeight;
            canvas.getContext('2d').drawImage(faceVideo, 0, 0, canvas.width, canvas.height);
            const image = await new Promise((resolve, reject) => {
                canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Could not capture an image.')), 'image/jpeg', 0.9);
            });
            const formData = new FormData(faceForm);
            formData.set('image', image, 'employee-face.jpg');
            const response = await fetch(@json(route('face.register.employee')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok || !result.success) {
                throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Face enrollment failed. Ensure exactly one face is visible and try again.');
            }

            faceStatus.textContent = 'Face profile enrolled successfully. The employee can now use face attendance scanning.';
            const enrollButton = document.querySelector('[data-bs-target="#employeeFaceModal"]');
            if (enrollButton) enrollButton.innerHTML = '<i class="bi bi-person-bounding-box me-1"></i>Update face profile';
            stopCamera();
        } catch (error) {
            faceStatus.textContent = error.message || 'Face enrollment failed. Please try again.';
            saveButton.disabled = !stream;
        }
    });

    window.addEventListener('beforeunload', stopCamera);
});
</script>
@endpush
