@extends('layouts.app')

@section('title', $student->exists ? 'Edit ' . $student->name_english : 'Add Student')

@php
    $v = fn($field, $model = null) => old($field, ($model ?? $student)->{$field} ?? '');
    $requestedStage = request()->query('student_stage');
    $stageValue = old('student_stage', in_array($requestedStage, array_keys(\App\Models\Student::STAGE_LABELS), true) ? $requestedStage : ($student->student_stage ?? ''));
    $requestedGraduationStatus = request()->query('graduation_status');
    $graduationStatusValue = old('graduation_status', in_array($requestedGraduationStatus, array_keys(\App\Models\Student::GRADUATION_STATUS_LABELS), true) ? $requestedGraduationStatus : ($student->graduation_status ?? 'not_graduated'));
    $ck = fn($field, $model = null) => old($field, ($model ?? $student)->{$field} ?? false) ? 'checked' : '';
    $sc = $studentCourse;
    $initialCourseId = old('sc_course_id', $sc->course_id ?? '');
    $initialCourse = $courses->firstWhere('id', (int) $initialCourseId) ?? $sc->course;
    $initialSemesterId = old('sc_semester_id', $sc->semester_id ?? '');
    $initialSemester = $semesters->firstWhere('id', (int) $initialSemesterId) ?? $sc->semester;
    $academicOptionData = [
        'courses' => $courses->map(fn ($course) => ['id' => $course->id, 'name' => $course->name, 'program' => $course->program?->name, 'category' => $course->category?->name_en ?: $course->course_category, 'category_year_id' => $course->category?->academic_year_id, 'category_semester_year_id' => $course->category?->semester?->academic_year_id])->values(),
        'academicYears' => $academicYears->map(fn ($year) => ['id' => $year->id, 'label' => $year->academic_year, 'campus' => $year->campus?->name, 'category' => $year->courseCategory?->name_en])->values(),
        'semesters' => $semesters->map(fn ($semester) => ['id' => $semester->id, 'name' => $semester->name, 'academic_year_id' => $semester->academic_year_id, 'term' => $semester->term])->values(),
        'intakes' => $intakes->map(fn ($intake) => ['year' => $intake->academic_year])->values(),
        'batches' => $batches->map(fn ($batch) => ['id' => $batch->id, 'name' => $batch->name, 'code' => $batch->code, 'course_id' => $batch->course_id, 'campus_id' => $batch->campus_id, 'intake_id' => $batch->intake_id, 'intake_year' => $batch->intake?->academic_year, 'semester_id' => $batch->semester_id, 'semester_year_id' => $batch->semester?->academic_year_id, 'study_time' => $batch->study_time])->values(),
    ];
    $academicInitialState = [
        'year' => old('sc_academic_year', $initialSemester?->academic_year_id ?? ''),
        'program' => $initialCourse ? ($initialCourse->program?->name ?: '__unassigned__') : '',
        'category' => $initialCourse ? (($initialCourse->category?->name_en ?: $initialCourse->course_category) ?: '__unassigned__') : '',
        'course' => $initialCourseId,
        'semesterId' => $initialSemesterId,
        'term' => old('sc_term', $initialSemester?->term ?? ''),
        'batch' => old('sc_batch_id', $sc->batch_id ?? ''),
        'intakeId' => old('sc_intake_id', $sc->intake_id ?? ''),
    ];
    $edu = $education;
    $emp = $employment;
    $selectedClubIds = old('school_club_ids', $selectedClubIds ?? []);
    $selectedStudyGroupIds = old('study_group_ids', $selectedStudyGroupIds ?? []);
    $studyGroupData = $studyGroups->mapWithKeys(fn ($g) => [(string) $g->id => [
        'class_group' => $g->name,
        'instructor' => $g->teacher?->fullName() ?? '',
        'study_time' => $g->study_time ?? '',
    ]])->all();
    $vsc = fn($f) => old('sc_' . $f, $sc->{$f} ?? '');
    $cksc = fn($f) => old('sc_' . $f, $sc->{$f} ?? false) ? 'checked' : '';
    $vedu = fn($f) => old('edu_' . $f, $edu->{str_starts_with($f, 'edu_') ? $f : $f} ?? '');
    $vemp = fn($f) => old('emp_' . $f, $emp->{$f} ?? '');
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title">
            <i class="bi bi-{{ $student->exists ? 'pencil-square' : 'person-plus' }} text-primary me-1"></i>
            {{ $student->exists ? 'Edit Student — ' . $student->name_english . ' (' . $student->student_code . ')' : 'Add New Student' }}
        </h1>
        <div class="page-subtitle">Fields marked <span class="required-mark">*</span> are required. All other fields are optional.</div>
        @if($student->exists)
            <div class="small text-warning-emphasis mt-1"><i class="bi bi-info-circle me-1"></i>Profile edits are sent to an administrator for approval. Use “Add Course” on the student profile to add a course immediately.</div>
        @endif
    </div>
    <a href="{{ $student->exists ? route('students.show', $student) : route('students.index') }}" class="btn btn-light btn-sm"><i class="bi bi-x-lg me-1"></i>Cancel</a>
</div>

<form method="POST" enctype="multipart/form-data" data-loading
      class="mx-auto"
      action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}">
    @csrf
    @if($student->exists) @method('PUT') @endif

    <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto" id="studentFormTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabA" type="button"><i class="bi bi-person me-1"></i>Personal</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabB" type="button"><i class="bi bi-mortarboard me-1"></i>Center Education</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabC" type="button"><i class="bi bi-book me-1"></i>Current Education</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabD" type="button"><i class="bi bi-briefcase me-1"></i>Career</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabF" type="button"><i class="bi bi-heart me-1"></i>Alumni Engagement</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabG" type="button"><i class="bi bi-gear me-1"></i>System</button></li>
    </ul>

    <div class="tab-content">
        <!-- ================= A. Personal ================= -->
        <div class="tab-pane fade show active" id="tabA">
            <div class="card mb-3">
                <div class="card-header">A. Basic Personal Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 text-center">
                            <label class="form-label">Student Photo</label>
                            <div class="mb-2">
                                @if($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" class="profile-avatar" alt="photo">
                                @else
                                    <span class="profile-avatar-placeholder bg-light text-primary border"><i class="bi bi-person"></i></span>
                                @endif
                            </div>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                            <div class="form-text">JPG/PNG, max 2MB</div>
                            @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                @if($student->exists)
                                <div class="col-md-4">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" class="form-control" value="{{ $student->student_code }}" readonly disabled>
                                    <div class="form-text">Auto-generated, unique</div>
                                </div>
                                @endif
                                <div class="col-md-4">
                                    <label class="form-label">English Full Name <span class="required-mark">*</span></label>
                                    <input type="text" name="name_english" class="form-control @error('name_english') is-invalid @enderror" value="{{ $v('name_english') }}" required>
                                    @error('name_english')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Khmer Full Name</label>
                                    <input type="text" name="name_khmer" class="form-control" value="{{ $v('name_khmer') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Chinese Name</label>
                                    <input type="text" name="name_chinese" class="form-control" value="{{ $v('name_chinese') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">— Select —</option>
                                        @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $k => $lbl)
                                            <option value="{{ $k }}" @selected($v('gender') === $k)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ $v('date_of_birth') ? \Illuminate\Support\Carbon::parse($v('date_of_birth'))->format('Y-m-d') : '' }}">
                                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="{{ $v('nationality') }}" placeholder="e.g. Cambodian">
                                </div>
                                @if(auth()->user()->canViewSensitiveData())
                                <div class="col-md-4">
                                    <label class="form-label">National ID / Passport <span class="badge bg-danger" style="font-size:.6rem">RESTRICTED</span></label>
                                    <input type="text" name="national_id" class="form-control" value="{{ $v('national_id') }}">
                                </div>
                                @elseif($student->exists && $student->national_id)
                                <div class="col-md-4">
                                    <label class="form-label">National ID / Passport</label>
                                    <input type="text" class="form-control" value="•••••• (restricted)" disabled>
                                    <input type="hidden" name="national_id" value="{{ $student->national_id }}">
                                </div>
                                @endif
                                <div class="col-md-4">
                                    <label class="form-label">Marital Status</label>
                                    <select name="marital_status" class="form-select">
                                        <option value="">— Select —</option>
                                        @foreach(['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'] as $k => $lbl)
                                            <option value="{{ $k }}" @selected($v('marital_status') === $k)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Personal Phone <span class="required-mark">*</span></label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $v('phone') }}" required>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Telegram Username / Contact</label>
                                    <input type="text" name="telegram" class="form-control" value="{{ $v('telegram') }}" placeholder="@username">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $v('email') }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Facebook Profile URL</label>
                                    <input type="url" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ $v('facebook_url') }}" placeholder="https://facebook.com/...">
                                    @error('facebook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">LinkedIn Profile URL</label>
                                    <input type="url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ $v('linkedin_url') }}" placeholder="https://linkedin.com/in/...">
                                    @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Current Residential Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ $v('address') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Province / City</label>
                                    <input type="text" name="province" class="form-control" value="{{ $v('province') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" value="{{ $v('country') }}" placeholder="Cambodia">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="form-section-title">Emergency Contact</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ $v('emergency_contact_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Relationship</label>
                            <select name="emergency_contact_relationship" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['Parent', 'Sibling', 'Spouse', 'Relative', 'Friend', 'Other'] as $lbl)
                                    <option value="{{ $lbl }}" @selected($v('emergency_contact_relationship') === $lbl)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" class="form-control" value="{{ $v('emergency_contact_phone') }}">
                        </div>
                    </div>

                    <hr>
                    <h6 class="form-section-title">Communication Preferences & Status</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Preferred Channel</label>
                            <select name="preferred_channel" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['phone_call' => 'Phone Call', 'telegram' => 'Telegram', 'email' => 'Email', 'sms' => 'SMS', 'facebook' => 'Facebook', 'in_person' => 'In Person'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected($v('preferred_channel') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preferred Language</label>
                            <select name="preferred_language" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['Khmer', 'English', 'Chinese', 'Other'] as $lbl)
                                    <option value="{{ $lbl }}" @selected($v('preferred_language') === $lbl)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Registered in CRM</label>
                            <input type="date" name="registered_date" class="form-control" value="{{ $v('registered_date') ? \Illuminate\Support\Carbon::parse($v('registered_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contact Status <span class="required-mark">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(\App\Models\Student::STATUS_LABELS as $k => $lbl)
                                    <option value="{{ $k }}" @selected($v('status') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lifecycle Stage <span class="required-mark">*</span></label>
                            <select name="student_stage" class="form-select @error('student_stage') is-invalid @enderror" required>
                                @foreach(\App\Models\Student::STAGE_LABELS as $key => $label)
                                    <option value="{{ $key }}" @selected($stageValue === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('student_stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Current Student, Alumni, or Dropped Out describes the lifecycle. Graduation outcome is selected separately.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Graduation Status <span class="required-mark">*</span></label>
                            <select name="graduation_status" class="form-select @error('graduation_status') is-invalid @enderror" required>
                                @foreach(\App\Models\Student::GRADUATION_STATUS_LABELS as $key => $label)
                                    <option value="{{ $key }}" @selected($graduationStatusValue === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('graduation_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Dashboard Graduate totals use this status; Alumni totals use Lifecycle Stage.</div>
                        </div>
                    </div>
                    @if($v('status') === 'do_not_contact')
                        <div class="alert alert-warning small mt-3 mb-0"><i class="bi bi-exclamation-triangle me-1"></i><strong>Do Not Contact:</strong> the system will block logging new outreach communications for this student.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ================= B. Center Education ================= -->
        <div class="tab-pane fade" id="tabB">
            <div class="card">
                <div class="card-header">B. Education Center Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Center Name</label>
                            <input type="text" class="form-control" value="{{ \App\Models\Setting::get('center_name', 'Education Center') }}" readonly disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Registration Number at Center</label>
                            <input type="text" name="sc_registration_number" class="form-control" value="{{ $vsc('registration_number') }}">
                        </div>
                        <div class="col-12 mt-4">
                            <h6 class="form-section-title mb-0">Enrollment selection</h6>
                            <p class="form-text mb-0">Choose Academic Year → Program → Course Category → Course → Semester → Term → Batch. Study Time is taken from the selected batch. Configure program/category on courses and study time on batches first.</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <select id="scAcademicYear" name="sc_academic_year" class="form-select @error('sc_academic_year') is-invalid @enderror">
                                <option value="">— Select academic year —</option>
                            </select>
                            @error('sc_academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Program</label>
                            <select id="scProgram" class="form-select" disabled>
                                <option value="program_id">— Select program —</option>
                            </select>
                            @error('sc_program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Course Category</label>
                            <select id="scCourseCategory" class="form-select" disabled>
                                <option value="">— Select category —</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Course</label>
                            <select id="scCourse" name="sc_course_id" class="form-select @error('sc_course_id') is-invalid @enderror" disabled>
                                <option value="">— Select course —</option>
                            </select>
                            @error('sc_course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <select id="scSemesterName" class="form-select" disabled>
                                <option value="">— Select semester —</option>
                            </select>
                            <input type="hidden" id="scSemesterId" name="sc_semester_id" value="{{ $vsc('semester_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Term</label>
                            <select id="scTerm" class="form-select @error('sc_term') is-invalid @enderror" disabled>
                                <option value="">— Select term —</option>
                            </select>
                            <input type="hidden" id="scTermValue" name="sc_term" value="{{ old('sc_term', $initialSemester?->term ?? '') }}">
                            @error('sc_term')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Batch</label>
                            <select id="scBatch" name="sc_batch_id" class="form-select @error('sc_batch_id') is-invalid @enderror" disabled>
                                <option value="">— Select batch —</option>
                            </select>
                            @error('sc_batch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <input type="hidden" id="scIntakeId" name="sc_intake_id" value="{{ $vsc('intake_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Study Time</label>
                            <input type="text" id="scStudyTime" class="form-control" value="" placeholder="From study group" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Campus / Branch</label>
                            <select id="scCampus" name="sc_campus_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($campuses as $c)
                                    <option value="{{ $c->id }}" @selected($vsc('campus_id') == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Major / Specialization</label>
                            <input type="text" name="sc_major" class="form-control" value="{{ $vsc('major') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Study Group</label>
                            <select id="scStudyGroup" name="sc_study_group_id" class="form-select @error('sc_study_group_id') is-invalid @enderror" aria-describedby="scStudyGroupHelp">
                                <option value="">— Select study group —</option>
                                @foreach($studyGroups as $group)
                                    <option value="{{ $group->id }}" @selected((string) $vsc('study_group_id') === (string) $group->id)>
                                        {{ $group->name }}{{ $group->code ? ' (' . $group->code . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="scStudyGroupHelp" class="form-text">Class / Group, Instructor and Study Time are filled from the selected study group.</div>
                            @error('sc_study_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Class / Group</label>
                            <input type="text" name="sc_class_group" id="scClassGroup" class="form-control" value="{{ $vsc('class_group') }}" placeholder="From study group" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Instructor / Teacher Name</label>
                            <input type="text" name="sc_instructor_name" id="scInstructorName" class="form-control" value="{{ $vsc('instructor_name') }}" placeholder="From study group" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Enrollment Date</label>
                            <input type="date" name="sc_enrollment_date" class="form-control" value="{{ $vsc('enrollment_date') ? \Illuminate\Support\Carbon::parse($vsc('enrollment_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course Start Date</label>
                            <input type="date" name="sc_course_start_date" class="form-control" value="{{ $vsc('course_start_date') ? \Illuminate\Support\Carbon::parse($vsc('course_start_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course Completion Date</label>
                            <input type="date" name="sc_course_completion_date" class="form-control" value="{{ $vsc('course_completion_date') ? \Illuminate\Support\Carbon::parse($vsc('course_completion_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Graduation Date</label>
                            <input type="date" name="sc_graduation_date" class="form-control" value="{{ $vsc('graduation_date') ? \Illuminate\Support\Carbon::parse($vsc('graduation_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Graduation Year</label>
                            <input type="number" name="sc_graduation_year" class="form-control @error('sc_graduation_year') is-invalid @enderror" min="1980" max="2100" value="{{ $vsc('graduation_year') }}">
                            @error('sc_graduation_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Attendance % (optional)</label>
                            <input type="number" step="0.01" min="0" max="100" name="sc_attendance_percentage" class="form-control @error('sc_attendance_percentage') is-invalid @enderror" value="{{ $vsc('attendance_percentage') }}">
                            @error('sc_attendance_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Performance / Grade</label>
                            <input type="text" name="sc_grade" class="form-control" value="{{ $vsc('grade') }}" placeholder="e.g. A, B+, 85%">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="sc_certificate_received" value="1" class="form-check-input" id="certRecv" {{ $cksc('certificate_received') }}>
                                <label class="form-check-label" for="certRecv">Certificate Received</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Certificate Number</label>
                            <input type="text" name="sc_certificate_number" class="form-control" value="{{ $vsc('certificate_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Scholarship Status</label>
                            <input type="text" name="sc_scholarship_status" class="form-control" value="{{ $vsc('scholarship_status') }}" placeholder="None / Partial / Full">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="sc_internship_participation" value="1" class="form-check-input" id="internPart" {{ $cksc('internship_participation') }}>
                                <label class="form-check-label" for="internPart">Participated in Internship</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Internship Company</label>
                            <input type="text" name="sc_internship_company" class="form-control" value="{{ $vsc('internship_company') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Graduation Project / Title</label>
                            <input type="text" name="sc_graduation_project" class="form-control" value="{{ $vsc('graduation_project') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reason for Leaving / Withdrawal</label>
                            <textarea name="sc_withdrawal_reason" class="form-control" rows="2">{{ $vsc('withdrawal_reason') }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Alumni Membership Status</label>
                            <select name="sc_alumni_membership_status" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['member' => 'Member', 'non_member' => 'Non-member', 'expired' => 'Expired'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected($vsc('alumni_membership_status') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Alumni Membership Date</label>
                            <input type="date" name="sc_alumni_membership_date" class="form-control" value="{{ $vsc('alumni_membership_date') ? \Illuminate\Support\Carbon::parse($vsc('alumni_membership_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Education Notes</label>
                            <textarea name="sc_notes" class="form-control" rows="2">{{ $vsc('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= C. Current Education ================= -->
        <div class="tab-pane fade" id="tabC">
            <div class="card">
                <div class="card-header">C. Current Education Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Currently Studying? <span class="required-mark">*</span></label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_currently_studying" id="studyYes" value="1" {{ old('is_currently_studying', $student->is_currently_studying) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="studyYes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_currently_studying" id="studyNo" value="0" {{ !old('is_currently_studying', $student->is_currently_studying ?? true) ? '' : '' }}>
                                    <label class="form-check-label" for="studyNo">No</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Further Education Status</label>
                            <select name="education_status" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(\App\Models\Student::EDUCATION_STATUS_LABELS as $k => $lbl)
                                    <option value="{{ $k }}" @selected($v('education_status') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current School / University</label>
                            <input type="text" name="edu_institution" class="form-control" value="{{ $vedu('institution') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Degree / Program</label>
                            <input type="text" name="edu_degree_program" class="form-control" value="{{ $vedu('degree_program') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Major / Field of Study</label>
                            <input type="text" name="edu_major" class="form-control" value="{{ $vedu('major') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Year / Level</label>
                            <input type="text" name="edu_year_level" class="form-control" value="{{ $vedu('year_level') }}" placeholder="e.g. Year 2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="edu_start_date" class="form-control" value="{{ $vedu('start_date') ? \Illuminate\Support\Carbon::parse($vedu('start_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expected Graduation Date</label>
                            <input type="date" name="edu_expected_graduation_date" class="form-control" value="{{ $vedu('expected_graduation_date') ? \Illuminate\Support\Carbon::parse($vedu('expected_graduation_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Scholarship Information</label>
                            <input type="text" name="edu_scholarship_info" class="form-control" value="{{ $vedu('scholarship_info') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Further Education Plans</label>
                            <textarea name="edu_further_plans" class="form-control" rows="2">{{ $edu->further_education_plans ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= D. Career ================= -->
        <div class="tab-pane fade" id="tabD">
            <div class="card mb-3">
                <div class="card-header">D. Employment & Career Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Employment Status</label>
                            <select name="employment_status" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(\App\Models\Student::EMPLOYMENT_STATUS_LABELS as $k => $lbl)
                                    <option value="{{ $k }}" @selected($v('employment_status') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Company / Organization</label>
                            <input type="text" name="emp_company" class="form-control" value="{{ $vemp('company') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Job Title / Position</label>
                            <input type="text" name="emp_job_title" class="form-control" value="{{ $vemp('job_title') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Industry</label>
                            <input type="text" name="emp_industry" class="form-control" value="{{ $vemp('industry') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <input type="text" name="emp_department" class="form-control" value="{{ $vemp('department') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Type</label>
                            <select name="emp_employment_type" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'freelance' => 'Freelance'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected($vemp('employment_type') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Company Address</label>
                            <input type="text" name="emp_company_address" class="form-control" value="{{ $vemp('company_address') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Work Phone</label>
                            <input type="tel" name="emp_work_phone" class="form-control" value="{{ $vemp('work_phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Work Email</label>
                            <input type="email" name="emp_work_email" class="form-control @error('emp_work_email') is-invalid @enderror" value="{{ $vemp('work_email') }}">
                            @error('emp_work_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Start Date</label>
                            <input type="date" name="emp_start_date" class="form-control" value="{{ $vemp('start_date') ? \Illuminate\Support\Carbon::parse($vemp('start_date'))->format('Y-m-d') : '' }}">
                        </div>
                        @if(auth()->user()->canViewSensitiveData())
                        <div class="col-md-4">
                            <label class="form-label">Salary Range <span class="badge bg-danger" style="font-size:.6rem">CONFIDENTIAL</span></label>
                            <input type="text" name="emp_salary_range" class="form-control" value="{{ $vemp('salary_range') }}">
                        </div>
                        @elseif($emp->salary_range)
                        <div class="col-md-4">
                            <label class="form-label">Salary Range</label>
                            <input type="text" class="form-control" value="•••••• (confidential)" disabled>
                            <input type="hidden" name="emp_salary_range" value="{{ $emp->salary_range }}">
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Professional Skills</label>
                            <input type="text" name="professional_skills" class="form-control" value="{{ $v('professional_skills') }}" placeholder="Comma separated">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Technical Skills</label>
                            <input type="text" name="technical_skills" class="form-control" value="{{ $v('technical_skills') }}" placeholder="Comma separated">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Certifications</label>
                            <textarea name="certifications" class="form-control" rows="2">{{ $v('certifications') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Career Goals</label>
                            <textarea name="career_goals" class="form-control" rows="2">{{ $v('career_goals') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Portfolio URL</label>
                            <input type="url" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror" value="{{ $v('portfolio_url') }}">
                            @error('portfolio_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CV / Resume Upload</label>
                            <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
                            @if($student->cv_path)<div class="form-text">Current: {{ basename($student->cv_path) }}</div>@endif
                            @error('cv')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Career Notes</label>
                            <textarea name="career_notes" class="form-control" rows="2">{{ $v('career_notes') }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <h6 class="form-section-title">Opportunity Interests</h6>
                    <div class="row g-2">
                        @foreach([
                            'interested_jobs' => 'Interested in job opportunities',
                            'interested_internships' => 'Interested in internships',
                            'interested_mentoring' => 'Interested in mentoring current students',
                            'interested_trainer' => 'Interested in becoming a trainer',
                            'interested_volunteering' => 'Interested in volunteering',
                        ] as $f => $lbl)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="{{ $f }}" value="1" class="form-check-input" id="{{ $f }}" {{ old($f, $student->{$f} ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="{{ $f }}">{{ $lbl }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= F. Engagement ================= -->
        <div class="tab-pane fade" id="tabF">
            <div class="card">
                <div class="card-header">F. Alumni Engagement Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Alumni Membership Number</label>
                            <input type="text" name="alumni_membership_number" class="form-control" value="{{ $v('alumni_membership_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alumni Group / Class</label>
                            <input type="text" name="alumni_group" class="form-control" value="{{ $v('alumni_group') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Engagement Status</label>
                            <select name="engagement_status" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['engaged' => 'Engaged', 'semi_engaged' => 'Semi-engaged', 'not_engaged' => 'Not Engaged'] as $k => $lbl)
                                    <option value="{{ $k }}" @selected($v('engagement_status') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Last Contacted Date</label>
                            <input type="date" name="last_contacted_date" class="form-control" value="{{ $v('last_contacted_date') ? \Illuminate\Support\Carbon::parse($v('last_contacted_date'))->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" name="next_followup_date" class="form-control" value="{{ $v('next_followup_date') ? \Illuminate\Support\Carbon::parse($v('next_followup_date'))->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <hr>
                    <h6 class="form-section-title">Communication Consent & Preferences</h6>
                    <div class="row g-2">
                        @foreach([
                            'interested_activities' => 'Interested in alumni activities',
                            'interested_newsletter' => 'Consent: receive newsletters',
                            'interested_job_announcements' => 'Consent: receive job announcements',
                            'interested_training' => 'Consent: receive training opportunities',
                        ] as $f => $lbl)
                            <div class="col-md-5">
                                <div class="form-check">
                                    <input type="checkbox" name="{{ $f }}" value="1" class="form-check-input" id="f_{{ $f }}" {{ old($f, $student->{$f} ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="f_{{ $f }}">{{ $lbl }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Referral Information</label>
                            <textarea name="referral_info" class="form-control" rows="2" placeholder="Who referred this student / referrals made by this student">{{ $v('referral_info') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alumni Feedback</label>
                            <textarea name="alumni_feedback" class="form-control" rows="2">{{ $v('alumni_feedback') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Engagement Notes</label>
                            <textarea name="engagement_notes" class="form-control" rows="2">{{ $v('engagement_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= G. System ================= -->
        <div class="tab-pane fade" id="tabG">
            <div class="card">
                <div class="card-header">G. System Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Study Groups</label>
                            @if($studyGroups->isEmpty())
                                <div class="form-text">No study groups have been created yet. Create one from Student Services → Study Groups.</div>
                            @else
                                <div class="border rounded p-2 d-flex flex-column gap-1" style="max-height: 180px; overflow-y: auto;">
                                    @foreach($studyGroups as $group)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="study_group_ids[]"
                                                   id="studyGroup{{ $group->id }}" value="{{ $group->id }}"
                                                   @checked(in_array((string) $group->id, array_map('strval', $selectedStudyGroupIds)))>
                                            <label class="form-check-label" for="studyGroup{{ $group->id }}">
                                                {{ $group->name }}{{ $group->code ? ' (' . $group->code . ')' : '' }}{{ $group->graduation_year ? ' — Class of ' . $group->graduation_year : '' }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="studyGroupsHelp" class="form-text">Tick every study group this student belongs to.</div>
                                @error('study_group_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @endif
                            @error('study_group_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">School Clubs</label>
                            @if($schoolClubs->isEmpty())
                                <div class="form-text">No clubs have been created. An administrator can add clubs from Administration → School Clubs.</div>
                            @else
                                <div class="row g-2">
                                    @foreach($schoolClubs as $club)
                                        <div class="col-md-4 col-sm-6">
                                            <label class="form-check border rounded p-2 ps-5">
                                                <input class="form-check-input" type="checkbox" name="school_club_ids[]" value="{{ $club->id }}" @checked(in_array($club->id, $selectedClubIds))>
                                                <span class="form-check-label">{{ $club->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @error('school_club_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Assigned Staff Member</label>
                            <select name="assigned_staff_id" class="form-select">
                                <option value="">— Unassigned —</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}" @selected(old('assigned_staff_id', $student->assigned_staff_id) == $s->id)>{{ $s->name }} ({{ $s->role?->display_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        @if($student->exists)
                        <div class="col-md-4">
                            <label class="form-label">Created By</label>
                            <input type="text" class="form-control" value="{{ $student->creator?->name ?? '—' }}" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Created Date</label>
                            <input type="text" class="form-control" value="{{ $student->created_at?->format('Y-m-d H:i') }}" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Updated By</label>
                            <input type="text" class="form-control" value="{{ $student->updater?->name ?? '—' }}" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Updated Date</label>
                            <input type="text" class="form-control" value="{{ $student->updated_at?->format('Y-m-d H:i') }}" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Record Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($student->record_status) }}" disabled>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label">Internal Notes (visible to staff only)</label>
                            <textarea name="internal_notes" class="form-control" rows="3">{{ $v('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($student->exists)
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-chat-left-text me-1"></i>Change request remarks</div>
            <div class="card-body">
                <label for="request_remarks" class="form-label">Explain what you changed and why <span class="text-muted">(optional)</span></label>
                <textarea id="request_remarks" name="request_remarks" class="form-control @error('request_remarks') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Briefly describe the requested changes for the approver">{{ old('request_remarks') }}</textarea>
                @error('request_remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a href="{{ $student->exists ? route('students.show', $student) : route('students.index') }}" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i>{{ $student->exists ? 'Submit for Approval' : 'Create Student' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const academicData = @json($academicOptionData);
    const academicYear = document.getElementById('scAcademicYear');
    const program = document.getElementById('scProgram');
    const category = document.getElementById('scCourseCategory');
    const course = document.getElementById('scCourse');
    const semesterName = document.getElementById('scSemesterName');
    const semesterId = document.getElementById('scSemesterId');
    const term = document.getElementById('scTerm');
    const termValue = document.getElementById('scTermValue');
    const batch = document.getElementById('scBatch');
    const intakeId = document.getElementById('scIntakeId');
    const campus = document.getElementById('scCampus');
    const studyTime = document.getElementById('scStudyTime');
    const initial = @json($academicInitialState);

    const addOptions = (select, placeholder, rows, valueOf, labelOf, selected = '') => {
        select.replaceChildren(new Option(placeholder, ''));
        rows.forEach((row) => select.add(new Option(labelOf(row), valueOf(row))));
        select.value = String(selected ?? '');
        select.disabled = rows.length === 0;
    };
    const unique = (values) => [...new Set(values.filter((value) => value !== null && value !== undefined && String(value) !== '').map(String))];
    const academicYears = academicData.academicYears;
    addOptions(
        academicYear,
        '— Select year —',
        academicYears,
        (row) => row.id,
        (row) => `${row.label}${row.campus ? ' — ' + row.campus : ''}${row.category ? ' · ' + row.category : ''}`,
        initial.year,
    );

    const programValue = (row) => row.program || '__unassigned__';
    const categoryValue = (row) => row.category || '__unassigned__';
    const labelUnassigned = (value) => value === '__unassigned__' ? 'Unassigned' : value;

    const coursesForSelectedYear = () => {
        if (!academicYear.value) return academicData.courses;

        return academicData.courses.filter((courseRow) => {
            const courseBatches = academicData.batches.filter((batchRow) => String(batchRow.course_id ?? '') === String(courseRow.id));
            const configuredYears = [courseRow.category_year_id, courseRow.category_semester_year_id,
                ...courseBatches.map((batchRow) => batchRow.semester_year_id)]
                .filter((year) => year !== null && year !== undefined && String(year) !== '')
                .map(String);

            return configuredYears.length === 0 || configuredYears.includes(academicYear.value);
        });
    };

    const fillPrograms = (selected = '') => {
        const values = unique(coursesForSelectedYear().map(programValue)).sort((a, b) => a.localeCompare(b));
        addOptions(program, '— Select program —', values, (value) => value, labelUnassigned, selected);
        program.disabled = values.length === 0;
        if (program.disabled) program.value = '';
        fillCategories(selected ? initial.category : '');
    };

    const fillCategories = (selected = '') => {
        const rows = coursesForSelectedYear().filter((row) => programValue(row) === program.value);
        const values = unique(rows.map(categoryValue)).sort((a, b) => a.localeCompare(b));
        addOptions(category, '— Select category —', values, (value) => value, labelUnassigned, selected);
        fillCourses(selected ? initial.course : '');
    };

    const fillCourses = (selected = '') => {
        const rows = coursesForSelectedYear().filter((row) => programValue(row) === program.value && categoryValue(row) === category.value);
        addOptions(course, '— Select course —', rows, (row) => row.id, (row) => row.name, selected);
        fillSemesters(selected ? initial.semesterId : '');
    };

    const fillSemesters = (selectedId = '') => {
        let rows = academicData.semesters.filter((row) => String(row.academic_year_id ?? '') === academicYear.value);
        if (course.value) {
            const courseSemesterIds = academicData.batches
                .filter((row) => String(row.course_id ?? '') === course.value
                    && String(row.semester_year_id ?? '') === academicYear.value)
                .map((row) => String(row.semester_id ?? ''))
                .filter(Boolean);
            if (courseSemesterIds.length) {
                rows = rows.filter((row) => courseSemesterIds.includes(String(row.id)));
            }
        }
        const selectedRow = rows.find((row) => String(row.id) === String(selectedId));
        addOptions(semesterName, '— Select semester —', rows, (row) => row.id, (row) => row.name, selectedRow?.id ?? '');
        fillTerms(selectedRow ? selectedRow.term : '', selectedRow?.id ?? '');
    };

    const fillTerms = (selectedTerm = '', selectedSemesterId = '') => {
        const selectedId = selectedSemesterId || semesterName.value;
        const rows = academicData.semesters.filter((row) => String(row.id) === String(selectedId));
        const terms = [...new Set(rows.map((row) => row.term === null ? '__none__' : String(row.term)))].sort((a, b) => Number(a) - Number(b));
        const selectedValue = selectedTerm === null || selectedTerm === '' ? (terms.includes('__none__') ? '__none__' : '') : String(selectedTerm);
        addOptions(term, '— Select term —', terms, (value) => value, (value) => value === '__none__' ? 'Not specified' : `Term ${value}`, selectedValue);
        termValue.value = term.value === '__none__' ? '' : term.value;
        const chosen = rows[0];
        semesterId.value = chosen?.id ?? '';
        fillBatches(initial.batch);
    };

    const fillBatches = (selectedId = '') => {
        const rows = academicData.batches.filter((row) => course.value && semesterId.value
            && String(row.course_id ?? '') === course.value
            && String(row.semester_id ?? '') === semesterId.value
            && (!campus.value || !row.campus_id || String(row.campus_id) === campus.value));
        addOptions(batch, '— Select batch —', rows, (row) => row.id, (row) => `${row.name} (${row.code})`, selectedId);
        const chosen = rows.find((row) => String(row.id) === batch.value);
        intakeId.value = chosen?.intake_id ?? '';
        studyTime.value = chosen?.study_time ?? '';
        if (studyGroupSel.value && studyGroupData[studyGroupSel.value]) studyTime.value = studyGroupData[studyGroupSel.value].study_time;
    };

    academicYear.addEventListener('change', () => fillPrograms());
    program.addEventListener('change', () => fillCategories());
    category.addEventListener('change', () => fillCourses());
    course.addEventListener('change', () => fillSemesters());
    semesterName.addEventListener('change', () => fillTerms());
    term.addEventListener('change', () => fillTerms(term.value));
    batch.addEventListener('change', () => fillBatches(batch.value));
    campus.addEventListener('change', () => fillBatches(batch.value));

    // Class / Group, Instructor and Study Time are driven by the selected study group.
    const studyGroupData = @json($studyGroupData);
    const studyGroupSel = document.getElementById('scStudyGroup');
    const classGroupInput = document.getElementById('scClassGroup');
    const instructorInput = document.getElementById('scInstructorName');
    const applyStudyGroup = () => {
        const data = studyGroupData[studyGroupSel.value];
        if (!data) return;
        classGroupInput.value = data.class_group;
        instructorInput.value = data.instructor;
        studyTime.value = data.study_time;
    };

    fillPrograms(initial.program);

    studyGroupSel.addEventListener('change', applyStudyGroup);
    applyStudyGroup();

    // Fix "Currently studying" radio default state on load
    const yes = document.getElementById('studyYes');
    const no = document.getElementById('studyNo');
    if (yes && no && !yes.checked) { no.checked = true; }
    // Jump to the tab containing validation errors
    const invalid = document.querySelector('.is-invalid');
    if (invalid) {
        const pane = invalid.closest('.tab-pane');
        if (pane) {
            const btn = document.querySelector(`[data-bs-target="#${pane.id}"]`);
            if (btn) new bootstrap.Tab(btn).show();
            invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>
@endpush
