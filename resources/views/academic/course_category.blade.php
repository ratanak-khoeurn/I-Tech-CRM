@extends('layouts.app')

@section('title', 'Course Categories')

@section('content')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

    <div>

        <h1 class="page-title">
            <i class="bi bi-collection text-primary me-1"></i>
            Course Categories
        </h1>

        <div class="page-subtitle">
            Manage course categories, programs, semesters and course options
        </div>

    </div>

    @if ($academicYears->isEmpty())

        <div class="text-end">
            <button type="button"
                    class="btn btn-secondary"
                    disabled
                    title="Create an academic year first">

                <i class="bi bi-lock me-1"></i>
                Add Course Category

            </button>
            <div class="small text-muted mt-1">
                <i class="bi bi-info-circle me-1"></i>
                You must
                <a href="{{ route('academic-years.index') }}" class="text-decoration-underline">
                    create an Academic Year
                </a>
                before adding a category.
            </div>
        </div>

    @else

        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addCategoryModal">

            <i class="bi bi-plus-lg me-1"></i>
            Add Course Category

        </button>

    @endif

</div>

@include('academic._tabs')


{{-- Filter --}}
<div class="card mb-3">

    <div class="card-body">

        <form method="GET"
              action="{{ route('course-categories.index') }}"
              class="row g-2 align-items-end">

            <div class="col-md-3">

                <label class="form-label small text-muted mb-1">
                    Search
                </label>

                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="Code or category name..."
                       value="{{ request('q') }}">

            </div>

            <div class="col-md-3">

                <label class="form-label small text-muted mb-1">
                    Program
                </label>

                <select name="program_id"
                        class="form-select">

                    <option value="">All Programs</option>

                    @foreach($programs as $program)

                        <option value="{{ $program->id }}"
                            @selected((int) request('program_id') === $program->id)>

                            {{ $program->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-2">

                <label class="form-label small text-muted mb-1">
                    Semester
                </label>

                <select name="semester_id"
                        class="form-select">

                    <option value="">All</option>

                    @foreach($semesters as $semester)

                        <option value="{{ $semester->id }}"
                            @selected((int) request('semester_id') === $semester->id)>

                            {{ $semester->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-2">

                <label class="form-label small text-muted mb-1">
                    Type
                </label>

                <select name="extra_course"
                        class="form-select">

                    <option value="">All</option>

                    <option value="1"
                        @selected(request('extra_course') === '1')>
                        Extra Course
                    </option>

                    <option value="0"
                        @selected(request('extra_course') === '0')>
                        Normal Course
                    </option>

                </select>

            </div>

            <div class="col-md-2 d-flex gap-2">

                <button class="btn btn-primary flex-grow-1">

                    <i class="bi bi-funnel me-1"></i>
                    Filter

                </button>

                <a href="{{ route('course-categories.index') }}"
                   class="btn btn-light"
                   title="Reset">

                    <i class="bi bi-x-lg"></i>

                </a>

            </div>

        </form>

    </div>

</div>


{{-- Table --}}
<div class="card">

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th>Code</th>
                    <th>Course Category</th>
                    <th>Khmer Name</th>
                    <th>Program</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th class="text-center">Order</th>
                    <th class="text-center">Extra</th>
                    <th class="text-center">Penalty</th>
                    <th class="text-end">Actions</th>

                </tr>

            </thead>

            <tbody>

            @forelse($categories as $category)

                <tr>

                    <td>

                        <span class="badge text-bg-secondary">
                            {{ $category->code }}
                        </span>

                    </td>

                    <td class="fw-semibold">

                        {{ $category->name_en }}

                    </td>

                    <td>

                        {{ $category->name_kh ?: '—' }}

                    </td>

                    <td>

                        {{ $category->program->name ?? '—' }}

                    </td>

                    <td>

                        {{ $category->academicYear->academic_year ?? '—' }}

                    </td>

                    <td>

                        {{ $category->semester->name ?? '—' }}

                    </td>

                    <td class="text-center">

                        {{ $category->sort_order }}

                    </td>

                    <td class="text-center">

                        @if($category->extra_course)

                            <span class="badge text-bg-info">
                                Yes
                            </span>

                        @else

                            <span class="badge text-bg-light text-dark">
                                No
                            </span>

                        @endif

                    </td>

                    <td class="text-center">

                        @if($category->penalty)

                            <span class="badge text-bg-warning">
                                Enabled
                            </span>

                        @else

                            <span class="badge text-bg-light text-dark">
                                Disabled
                            </span>

                        @endif

                    </td>

                    <td class="text-end text-nowrap">

                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                title="Edit"
                                data-bs-toggle="modal"
                                data-bs-target="#editCategoryModal{{ $category->id }}">

                            <i class="bi bi-pencil"></i>

                        </button>

                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                title="Delete"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteCategoryModal{{ $category->id }}">

                            <i class="bi bi-trash"></i>

                        </button>

                    </td>

                </tr>


                {{-- ================= EDIT MODAL ================= --}}
                <div class="modal fade"
                     id="editCategoryModal{{ $category->id }}"
                     tabindex="-1"
                     aria-hidden="true">

                    <div class="modal-dialog modal-lg modal-dialog-centered">

                        <div class="modal-content">

                            <form method="POST"
                                  action="{{ route('course-categories.update', $category->id) }}">

                                @csrf
                                @method('PUT')

                                <div class="modal-header">

                                    <h5 class="modal-title">

                                        <i class="bi bi-pencil-square text-primary me-1"></i>

                                        Edit Course Category

                                    </h5>

                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"></button>

                                </div>

                                <div class="modal-body">

                                    <div class="row g-3">

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Code <span class="text-danger">*</span>
                                            </label>

                                            <input type="text"
                                                   name="code"
                                                   class="form-control"
                                                   value="{{ $category->code }}"
                                                   required>

                                        </div>

                                        <div class="col-md-8">

                                            <label class="form-label">
                                                Name (EN)
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text"
                                                   name="name_en"
                                                   class="form-control"
                                                   value="{{ $category->name_en }}"
                                                   required>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Name (KH)
                                            </label>

                                            <input type="text"
                                                   name="name_kh"
                                                   class="form-control"
                                                   value="{{ $category->name_kh }}">

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Name Import
                                            </label>

                                            <input type="text"
                                                   name="name_import"
                                                   class="form-control"
                                                   value="{{ $category->name_import }}">

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Program
                                            </label>

                                            <select name="program_id"
                                                    class="form-select">

                                                <option value="">
                                                    Select Program
                                                </option>

                                                @foreach($programs as $program)

                                                    <option value="{{ $program->id }}"
                                                        @selected($category->program_id == $program->id)>

                                                        {{ $program->name }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Academic Year <span class="text-danger">*</span>
                                            </label>

                                            <select name="academic_year_id"
                                                    class="form-select"
                                                    required>

                                                <option value="">
                                                    Select Academic Year
                                                </option>

                                                @foreach($academicYears as $year)

                                                    <option value="{{ $year->id }}"
                                                        @selected($category->academic_year_id == $year->id)>

                                                        {{ $year->academic_year }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Semester
                                            </label>

                                            <select name="semester_id"
                                                    class="form-select">

                                                <option value="">
                                                    Select Semester
                                                </option>

                                                @foreach($semesters as $semester)

                                                    <option value="{{ $semester->id }}"
                                                        @selected($category->semester_id == $semester->id)>

                                                        {{ $semester->name }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">
                                                Order
                                            </label>

                                            <input type="number"
                                                   name="sort_order"
                                                   class="form-control"
                                                   min="0"
                                                   value="{{ $category->sort_order }}">

                                        </div>

                                        <div class="col-md-4">

                                            <div class="form-check form-switch mt-4">

                                                <input type="checkbox"
                                                       name="extra_course"
                                                       value="1"
                                                       class="form-check-input"
                                                       id="editExtra{{ $category->id }}"
                                                       @checked($category->extra_course)>

                                                <label class="form-check-label"
                                                       for="editExtra{{ $category->id }}">

                                                    Extra Course

                                                </label>

                                            </div>

                                        </div>

                                        <div class="col-md-4">

                                            <div class="form-check form-switch mt-4">

                                                <input type="checkbox"
                                                       name="penalty"
                                                       value="1"
                                                       class="form-check-input"
                                                       id="editPenalty{{ $category->id }}"
                                                       @checked($category->penalty)>

                                                <label class="form-check-label"
                                                       for="editPenalty{{ $category->id }}">

                                                    Enable Penalty

                                                </label>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button type="button"
                                            class="btn btn-light"
                                            data-bs-dismiss="modal">

                                        Cancel

                                    </button>

                                    <button type="submit"
                                            class="btn btn-primary">

                                        <i class="bi bi-check-lg me-1"></i>
                                        Update

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>


                {{-- ================= DELETE MODAL ================= --}}
                <div class="modal fade"
                     id="deleteCategoryModal{{ $category->id }}"
                     tabindex="-1"
                     aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title text-danger">

                                    <i class="bi bi-exclamation-triangle me-1"></i>

                                    Delete Course Category

                                </h5>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <p class="mb-2">
                                    Are you sure you want to delete:
                                </p>

                                <strong>
                                    {{ $category->name_en }}
                                </strong>

                                <p class="text-muted small mt-2 mb-0">
                                    This action cannot be undone.
                                </p>

                            </div>

                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-light"
                                        data-bs-dismiss="modal">

                                    Cancel

                                </button>

                                <form method="POST"
                                      action="{{ route('course-categories.destroy', $category->id) }}">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger">

                                        <i class="bi bi-trash me-1"></i>
                                        Delete

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <tr>

                    <td colspan="10">

                        <div class="empty-state py-5">

                            <i class="bi bi-collection"></i>

                            <p class="mb-0">
                                No course categories yet.
                                Add your first course category.
                            </p>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    @if($categories->hasPages())

        <div class="card-footer bg-transparent">

            {{ $categories->links() }}

        </div>

    @endif

</div>


{{-- ================= ADD CATEGORY MODAL ================= --}}
<div class="modal fade"
     id="addCategoryModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form method="POST"
                  action="{{ route('course-categories.store') }}">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-plus-circle text-primary me-1"></i>

                        Add Course Category

                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                Code <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="code"
                                   class="form-control"
                                   placeholder="e.g. MATH"
                                   required>

                        </div>

                        <div class="col-md-8">

                            <label class="form-label">
                                Name (EN)
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="name_en"
                                   class="form-control"
                                   placeholder="e.g. Mathematics"
                                   required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Name (KH)
                            </label>

                            <input type="text"
                                   name="name_kh"
                                   class="form-control"
                                   placeholder="ឈ្មោះជាភាសាខ្មែរ">

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Name Import
                            </label>

                            <input type="text"
                                   name="name_import"
                                   class="form-control"
                                   placeholder="Import name">

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                Program
                            </label>

                            <select name="program_id"
                                    class="form-select">

                                <option value="">
                                    Select Program
                                </option>

                                @foreach($programs as $program)

                                    <option value="{{ $program->id }}">
                                        {{ $program->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                Academic Year <span class="text-danger">*</span>
                            </label>

                            <select name="academic_year_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Select Academic Year
                                </option>

                                @foreach($academicYears as $year)

                                    <option value="{{ $year->id }}">
                                        {{ $year->academic_year }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                Semester
                            </label>

                            <select name="semester_id"
                                    class="form-select">

                                <option value="">
                                    Select Semester
                                </option>

                                @foreach($semesters as $semester)

                                    <option value="{{ $semester->id }}">
                                        {{ $semester->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                Order
                            </label>

                            <input type="number"
                                   name="sort_order"
                                   class="form-control"
                                   min="0"
                                   value="0">

                        </div>

                        <div class="col-md-4">

                            <div class="form-check form-switch mt-4">

                                <input type="checkbox"
                                       name="extra_course"
                                       value="1"
                                       class="form-check-input"
                                       id="extraCourse">

                                <label class="form-check-label"
                                       for="extraCourse">

                                    Extra Course

                                </label>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-check form-switch mt-4">

                                <input type="checkbox"
                                       name="penalty"
                                       value="1"
                                       class="form-check-input"
                                       id="enablePenalty">

                                <label class="form-check-label"
                                       for="enablePenalty">

                                    Enable Penalty

                                </label>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="bi bi-check-lg me-1"></i>

                        Save Course Category

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
