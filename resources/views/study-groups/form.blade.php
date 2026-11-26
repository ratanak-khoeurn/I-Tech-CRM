@extends('layouts.app')

@section('title', $studyGroup->exists ? 'Edit Study Group' : 'Create Study Group')

@section('content')
@php
    $formInitial = [
        'campus' => old('campus_id', $studyGroup->campus_id),
        'category' => old('course_category_id', $studyGroup->course_category_id),
        'year' => old('academic_year_id', $studyGroup->academic_year_id),
        'program' => old('program_id', $studyGroup->program_id),
        'intake' => old('intake_id', $studyGroup->intake_id),
        'allCourses' => (string) old('all_courses', $studyGroup->exists ? (int) $studyGroup->all_courses : 0),
        'courseIds' => collect(old('course_ids', $studyGroup->exists ? $studyGroup->courses->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all(),
    ];
    $academicOptions = [
        'categories' => $courseCategories->map(fn ($category) => [
            'id' => $category->id,
            'name' => $category->name_en,
            'programId' => $category->program_id,
            'yearId' => $category->academic_year_id,
        ])->values(),
        'years' => $academicYears->map(fn ($year) => [
            'id' => $year->id,
            'label' => $year->academic_year,
            'campusId' => $year->campus_id,
            'categoryId' => $year->course_category_id,
        ])->values(),
        'programs' => $programs->map(fn ($program) => ['id' => $program->id, 'name' => $program->name])->values(),
        'courses' => $courses->map(fn ($course) => ['id' => $course->id, 'name' => $course->name, 'code' => $course->code, 'programId' => $course->program_id, 'categoryId' => $course->category_id])->values(),
        'intakes' => $intakes->map(fn ($intake) => ['id' => $intake->id, 'name' => $intake->name, 'code' => $intake->code, 'year' => $intake->academic_year])->values(),
    ];
@endphp

<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-people-fill text-primary me-1"></i>{{ $studyGroup->exists ? 'Edit Study Group' : 'Add Study Group' }}</h1>
    <div class="page-subtitle">Choose the academic cohort and courses for this study group.</div>
</div>

<div class="card" style="max-width: 820px;">
    <div class="card-header">{{ $studyGroup->exists ? 'Edit Study Group' : 'Add Study Group' }}</div>
    <div class="card-body p-3 p-md-4">
        <form method="POST" action="{{ $studyGroup->exists ? route('study-groups.update', $studyGroup) : route('study-groups.store') }}" data-loading>
            @csrf
            @if($studyGroup->exists) @method('PUT') @endif

            <div class="row g-3 align-items-center mb-2">
                <label for="campus_id" class="col-md-4 col-form-label">Campus <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select id="campus_id" name="campus_id" class="form-select @error('campus_id') is-invalid @enderror" required>
                        <option value="">Campus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected((string) $formInitial['campus'] === (string) $campus->id)>{{ $campus->name }}{{ $campus->code ? ' (' . $campus->code . ')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('campus_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="course_category_id" class="col-md-4 col-form-label">Course Category <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select id="course_category_id" name="course_category_id" class="form-select @error('course_category_id') is-invalid @enderror" required>
                        <option value="">Course Category</option>
                        @foreach($courseCategories as $category)
                            <option value="{{ $category->id }}" @selected((string) $formInitial['category'] === (string) $category->id)>{{ $category->name_en }}</option>
                        @endforeach
                    </select>
                    @error('course_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="academic_year_id" class="col-md-4 col-form-label">Academic Year <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select id="academic_year_id" name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                        <option value="">Academic Year</option>
                    </select>
                    @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="program_id" class="col-md-4 col-form-label">Program <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select id="program_id" name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                        <option value="">Program</option>
                    </select>
                    @error('program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="all_courses" class="col-md-4 col-form-label">Course Selection <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select id="all_courses" name="all_courses" class="form-select @error('all_courses') is-invalid @enderror" required>
                        <option value="0" @selected($formInitial['allCourses'] === '0')>Select by course</option>
                        <option value="1" @selected($formInitial['allCourses'] === '1')>All courses in this program</option>
                    </select>
                    @error('all_courses')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Choose specific courses or include every course in the selected program.</div>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2" id="specificCoursesRow">
                <label for="course_ids" class="col-md-4 col-form-label">Courses <span class="text-danger course-required-mark">*</span></label>
                <div class="col-md-7">
                    <select id="course_ids" name="course_ids[]" class="form-select @error('course_ids') is-invalid @enderror" multiple size="4" aria-describedby="coursesHelp">
                    </select>
                    <div id="coursesHelp" class="form-text">Choose one or more courses for this study group. Use Ctrl (Windows) or Command (Mac) for multiple selections.</div>
                    @error('course_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('course_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="intake_id" class="col-md-4 col-form-label">Intake <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <select id="intake_id" name="intake_id" class="form-select @error('intake_id') is-invalid @enderror" required>
                        <option value="">Intake</option>
                    </select>
                    @error('intake_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="teacher_id" class="col-md-4 col-form-label">Teacher</label>
                <div class="col-md-7">
                    <select id="teacher_id" name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror">
                        <option value="">Select teacher (optional)</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $studyGroup->teacher_id) === (string) $teacher->id)>
                                {{ $teacher->fullName() }}{{ $teacher->position ? ' — ' . $teacher->position->title : '' }}{{ $teacher->employee_code ? ' (' . $teacher->employee_code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($teachers->isEmpty())<div class="form-text">No active employees are available. Add or activate a teacher/employee first.</div>@endif
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="study_time" class="col-md-4 col-form-label">Study Time</label>
                <div class="col-md-7">
                    <input type="text" id="study_time" name="study_time" class="form-control @error('study_time') is-invalid @enderror" value="{{ old('study_time', $studyGroup->study_time) }}" placeholder="e.g. Morning 7:00–9:00" maxlength="80">
                    @error('study_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Shown on student enrollments that use this group.</div>
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="code" class="col-md-4 col-form-label">Group Code <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $studyGroup->code) }}" placeholder="Group Code" required maxlength="60">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 align-items-center mb-2">
                <label for="name" class="col-md-4 col-form-label">Group Name <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $studyGroup->name) }}" placeholder="Group Name" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-7 offset-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-1"></i>{{ $studyGroup->exists ? 'Save Changes' : 'Save' }}</button>
                    <a href="{{ $studyGroup->exists ? route('study-groups.show', $studyGroup) : route('study-groups.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const options = @json($academicOptions);
    const initial = @json($formInitial);
    const campus = document.getElementById('campus_id');
    const category = document.getElementById('course_category_id');
    const year = document.getElementById('academic_year_id');
    const program = document.getElementById('program_id');
    const allCourses = document.getElementById('all_courses');
    const courseSelect = document.getElementById('course_ids');
    const coursesRow = document.getElementById('specificCoursesRow');
    const intake = document.getElementById('intake_id');

    function setOptions(select, placeholder, rows, valueOf, labelOf, selected = '') {
        select.replaceChildren(new Option(placeholder, ''));
        rows.forEach(function (row) {
            const option = new Option(labelOf(row), valueOf(row));
            option.selected = String(valueOf(row)) === String(selected);
            select.add(option);
        });
        select.disabled = rows.length === 0;
    }

    function yearsForSelection() {
        return options.years.filter((row) => String(row.campusId) === campus.value
            && String(row.categoryId) === category.value);
    }

    function programsForSelection() {
        const categoryRow = options.categories.find((row) => String(row.id) === category.value);
        const courseProgramIds = options.courses
            .filter((row) => String(row.categoryId) === category.value)
            .map((row) => String(row.programId));
        const allowedIds = categoryRow?.programId
            ? [String(categoryRow.programId)]
            : [...new Set(courseProgramIds)];

        return options.programs.filter((row) => allowedIds.includes(String(row.id)));
    }

    function refreshCourses(selected = []) {
        const rows = options.courses.filter((row) => String(row.categoryId) === category.value
            && String(row.programId) === program.value);
        setOptions(courseSelect, 'Courses', rows, (row) => row.id,
            (row) => `${row.name}${row.code ? ' (' + row.code + ')' : ''}`);
        Array.from(courseSelect.options).forEach(function (option) {
            if (selected.map(String).includes(option.value)) option.selected = true;
        });
        courseSelect.required = allCourses.value === '0';
    }

    function refreshIntakes(selected = '') {
        const academicYear = options.years.find((row) => String(row.id) === year.value);
        const academicYears = String(academicYear?.label || '').match(/\d{4}/g) || [];
        const matchingRows = options.intakes.filter((row) =>
            !row.year || academicYears.includes(String(row.year))
        );
        // Some intake records have a single-year value while the academic year
        // is stored as a range. Fall back to all intakes rather than blocking selection.
        const rows = matchingRows.length ? matchingRows : options.intakes;
        setOptions(intake, 'Intake', rows, (row) => row.id,
            (row) => `${row.name}${row.code ? ' (' + row.code + ')' : ''}`, selected);
    }

    function refreshAcademicOptions(useInitial = false) {
        const selectedYear = useInitial ? initial.year : '';
        const selectedProgram = useInitial ? initial.program : '';
        const selectedCourses = useInitial ? initial.courseIds : [];
        const selectedIntake = useInitial ? initial.intake : '';

        const yearRows = yearsForSelection();
        setOptions(year, 'Academic Year', yearRows, (row) => row.id, (row) => row.label, selectedYear);

        const programRows = programsForSelection();
        setOptions(program, 'Program', programRows, (row) => row.id, (row) => row.name, selectedProgram);

        refreshCourses(selectedCourses);
        refreshIntakes(selectedIntake);
    }

    function toggleCourseScope() {
        const selectingSpecificCourses = allCourses.value === '0';
        coursesRow.hidden = !selectingSpecificCourses;
        courseSelect.required = selectingSpecificCourses;
        courseSelect.disabled = !selectingSpecificCourses;
    }

    campus.addEventListener('change', () => refreshAcademicOptions());
    category.addEventListener('change', () => refreshAcademicOptions());
    year.addEventListener('change', () => refreshIntakes());
    program.addEventListener('change', () => refreshCourses());
    allCourses.addEventListener('change', toggleCourseScope);

    refreshAcademicOptions(true);
    toggleCourseScope();
});
</script>
@endpush
@endsection
