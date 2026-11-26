<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Employee;
use App\Models\Intake;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudyGroupController extends Controller
{
    public function index()
    {
        $groups = StudyGroup::with(['campus', 'courseCategory', 'academicYear', 'program', 'intake', 'teacher'])
            ->withCount([
            'students',
            'students as graduated_students_count' => fn ($query) => $query->where('graduation_status', 'graduated'),
        ])->orderBy('graduation_year')->orderBy('name')->paginate(15);

        return view('study-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('study-groups.form', $this->formData(new StudyGroup()));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request));
        $this->validateAcademicSelections($data);
        $courseIds = $data['course_ids'] ?? [];
        unset($data['course_ids']);
        $data['graduation_year'] = $this->graduationYearFor($data['academic_year_id']);
        $data['all_courses'] = $request->boolean('all_courses');

        $studyGroup = DB::transaction(function () use ($data, $courseIds) {
            $studyGroup = StudyGroup::create($data);
            $studyGroup->courses()->sync($courseIds);

            return $studyGroup;
        });

        AuditLog::record('created', 'study_group', $studyGroup->id, "Created study group '{$studyGroup->name}'");

        return redirect()->route('study-groups.show', $studyGroup)->with('success', 'Study group created. Add students to the group to manage graduation.');
    }

    public function show(Request $request, StudyGroup $studyGroup)
    {
        $members = $studyGroup->students()
            ->with(['studentCourses' => fn ($query) => $query->with('course')->orderByDesc('id')])
            ->orderBy('name_english')
            ->paginate(25, ['students.*'], 'members_page');

        $studentQuery = $this->eligibleStudentsQuery($studyGroup)
            ->whereDoesntHave('studyGroups', fn ($query) => $query->where('study_groups.id', $studyGroup->id));

        if ($search = trim((string) $request->query('student_q'))) {
            $studentQuery->where(function ($query) use ($search) {
                $query->where('name_english', 'like', "%{$search}%")
                    ->orWhere('name_khmer', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        $availableStudents = $studentQuery->orderBy('name_english')
            ->paginate(15, ['students.*'], 'students_page')
            ->withQueryString();

        return view('study-groups.show', compact('studyGroup', 'members', 'availableStudents'));
    }

    public function edit(StudyGroup $studyGroup)
    {
        $studyGroup->load(['courses', 'teacher']);

        return view('study-groups.form', $this->formData($studyGroup));
    }

    public function update(Request $request, StudyGroup $studyGroup)
    {
        $data = $request->validate($this->rules($request, $studyGroup));
        $this->validateAcademicSelections($data);
        $courseIds = $data['course_ids'] ?? [];
        unset($data['course_ids']);
        $data['graduation_year'] = $this->graduationYearFor($data['academic_year_id']);
        $data['all_courses'] = $request->boolean('all_courses');

        DB::transaction(function () use ($studyGroup, $data, $courseIds) {
            $studyGroup->update($data);
            $studyGroup->courses()->sync($courseIds);
        });

        AuditLog::record('updated', 'study_group', $studyGroup->id, "Updated study group '{$studyGroup->name}'");

        return redirect()->route('study-groups.show', $studyGroup)->with('success', 'Study group updated.');
    }

    public function destroy(StudyGroup $studyGroup)
    {
        $groupId = $studyGroup->id;
        $groupName = $studyGroup->name;
        $studyGroup->delete();

        AuditLog::record('deleted', 'study_group', $groupId, "Deleted study group '{$groupName}'");

        return redirect()->route('study-groups.index')->with('success', 'Study group deleted. Student records were not deleted.');
    }

    public function addStudent(Request $request, StudyGroup $studyGroup)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('record_status', 'active')],
        ]);

        $student = $this->eligibleStudentsQuery($studyGroup)->findOrFail($data['student_id']);

        $studyGroup->students()->syncWithoutDetaching([$student->id]);

        AuditLog::record('added', 'study_group_student', $studyGroup->id, "Added student #{$student->id} to study group '{$studyGroup->name}'");

        return back()->with('success', "{$student->name_english} added to the study group.");
    }

    public function removeStudent(StudyGroup $studyGroup, Student $student)
    {
        $studyGroup->students()->detach($student->id);

        AuditLog::record('removed', 'study_group_student', $studyGroup->id, "Removed student #{$student->id} from study group '{$studyGroup->name}'");

        return back()->with('success', 'Student removed from the study group.');
    }

    public function graduateSelected(Request $request, StudyGroup $studyGroup)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct'],
            'graduation_date' => ['nullable', 'date'],
            'graduation_year' => ['nullable', 'integer', 'min:1980', 'max:2100'],
        ]);

        $students = $studyGroup->students()
            ->whereIn('students.id', $data['student_ids'])
            ->get();

        if ($students->count() !== count($data['student_ids'])) {
            return back()->withErrors([
                'student_ids' => 'Select only students who belong to this group.',
            ])->withInput();
        }

        if ($students->contains(fn (Student $student) => $student->graduation_status === 'graduated')) {
            return back()->withErrors([
                'student_ids' => 'One or more selected students have already graduated.',
            ])->withInput();
        }

        $graduationDate = $data['graduation_date'] ?? now()->toDateString();
        $graduationYear = $data['graduation_year'] ?? ($studyGroup->graduation_year ?: (int) substr($graduationDate, 0, 4));

        DB::transaction(function () use ($students, $graduationDate, $graduationYear, $studyGroup) {
            foreach ($students as $student) {
                $student->forceFill([
                    'graduation_status' => 'graduated',
                    'student_stage' => 'alumni',
                    'education_status' => 'graduated',
                    'is_currently_studying' => false,
                ])->save();

                $enrollment = $student->latestStudentCourse();
                if ($enrollment) {
                    $enrollment->enrollment_status = 'completed';
                    $enrollment->graduation_date = $graduationDate;
                    $enrollment->graduation_year = $graduationYear;
                    $enrollment->course_completion_date = $enrollment->course_completion_date ?: $graduationDate;
                    $enrollment->save();
                }

                AuditLog::record('graduated', 'student', $student->id, "Marked {$student->name_english} as graduated from study group '{$studyGroup->name}'");
            }
        });

        AuditLog::record('graduated', 'study_group', $studyGroup->id, 'Graduated ' . $students->count() . " students from study group '{$studyGroup->name}'");

        return back()->with('success', $students->count() . ' student(s) marked as graduated.');
    }

    private function formData(StudyGroup $studyGroup): array
    {
        return [
            'studyGroup' => $studyGroup,
            'campuses' => Campus::orderBy('name')->get(['id', 'name', 'code']),
            'courseCategories' => CourseCategory::orderBy('name_en')->get(['id', 'name_en', 'program_id', 'academic_year_id']),
            'academicYears' => AcademicYear::with('courseCategory:id,name_en')
                ->orderByDesc('academic_year')->get(['id', 'academic_year', 'campus_id', 'course_category_id']),
            'programs' => Program::orderBy('name')->get(['id', 'name']),
            'courses' => Course::orderBy('name')->get(['id', 'name', 'code', 'program_id', 'category_id']),
            'intakes' => Intake::orderByDesc('academic_year')->orderBy('name')->get(['id', 'name', 'code', 'academic_year']),
            'teachers' => Employee::with('position:id,title')
                ->where('status', 'active')
                ->orderBy('first_name')->orderBy('last_name')->get(),
        ];
    }

    private function eligibleStudentsQuery(StudyGroup $studyGroup)
    {
        $courseIds = $studyGroup->all_courses ? [] : $studyGroup->courses()->pluck('courses.id')->all();

        return Student::query()
            ->with(['studentCourses' => fn ($query) => $query->with('course')->orderByDesc('id')])
            ->where('record_status', 'active')
            ->where('student_stage', 'current_student')
            ->where(function ($query) {
                $query->whereNull('graduation_status')
                    ->orWhere('graduation_status', '!=', 'graduated');
            })
            ->whereHas('studentCourses', function ($query) use ($studyGroup, $courseIds) {
                $query->where('campus_id', $studyGroup->campus_id)
                    ->where('intake_id', $studyGroup->intake_id)
                    ->whereHas('course', function ($courseQuery) use ($studyGroup, $courseIds) {
                        $courseQuery->where('category_id', $studyGroup->course_category_id)
                            ->where('program_id', $studyGroup->program_id);

                        if (!$studyGroup->all_courses) {
                            $courseQuery->whereIn('id', $courseIds ?: [0]);
                        }
                    })
                    ->where(function ($yearQuery) use ($studyGroup) {
                        $yearQuery->whereHas('semester', fn ($semesterQuery) => $semesterQuery->where('academic_year_id', $studyGroup->academic_year_id))
                            ->orWhereHas('batch.semester', fn ($semesterQuery) => $semesterQuery->where('academic_year_id', $studyGroup->academic_year_id))
                            ->orWhereHas('course.category', fn ($categoryQuery) => $categoryQuery->where('academic_year_id', $studyGroup->academic_year_id))
                            ->orWhere(function ($unassignedYearQuery) {
                                $unassignedYearQuery->whereDoesntHave('semester')
                                    ->whereDoesntHave('batch.semester')
                                    ->whereDoesntHave('course.category', fn ($categoryQuery) => $categoryQuery->whereNotNull('academic_year_id'));
                            });
                    });
            });
    }

    private function graduationYearFor(int $academicYearId): ?int
    {
        $label = AcademicYear::whereKey($academicYearId)->value('academic_year');

        preg_match_all('/\b(19|20|21)\d{2}\b/', (string) $label, $matches);

        return !empty($matches[0]) ? (int) end($matches[0]) : null;
    }

    private function validateAcademicSelections(array $data): void
    {
        $category = CourseCategory::findOrFail($data['course_category_id']);
        $hasProgramCourses = Course::where('category_id', $category->id)
            ->where('program_id', $data['program_id'])
            ->exists();

        if (($category->program_id && (int) $category->program_id !== (int) $data['program_id']) || !$hasProgramCourses) {
            throw ValidationException::withMessages([
                'program_id' => 'Select a program that has courses in this category.',
            ]);
        }

        $academicYear = AcademicYear::findOrFail($data['academic_year_id']);
        $intake = Intake::findOrFail($data['intake_id']);
        preg_match_all('/\b(19|20|21)\d{2}\b/', $academicYear->academic_year, $matches);
        $academicYearValues = $matches[0] ?? [];

        if ($intake->academic_year
            && $academicYearValues
            && !in_array((string) $intake->academic_year, $academicYearValues, true)) {
            throw ValidationException::withMessages([
                'intake_id' => 'Select an intake belonging to the selected academic year.',
            ]);
        }
    }

    private function rules(Request $request, ?StudyGroup $studyGroup = null): array
    {
        $categoryId = $request->input('course_category_id');
        $programId = $request->input('program_id');
        $allCourses = $request->input('all_courses', '1');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:60', Rule::unique('study_groups', 'code')->ignore($studyGroup?->id)],
            'campus_id' => CampusContext::campusRules(),
            'course_category_id' => ['required', 'integer', 'exists:course_categories,id'],
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')
                    ->where('campus_id', $request->input('campus_id'))
                    ->where('course_category_id', $categoryId),
            ],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'all_courses' => ['required', 'boolean'],
            'course_ids' => [Rule::requiredIf((string) $allCourses === '0'), 'nullable', 'array', 'min:1'],
            'course_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('courses', 'id')
                    ->where('category_id', $categoryId)
                    ->where('program_id', $programId),
            ],
            'intake_id' => ['required', 'integer', 'exists:intakes,id'],
            'teacher_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('status', 'active')],
            'study_time' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
