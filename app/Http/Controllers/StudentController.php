<?php

namespace App\Http\Controllers;

use App\Models\AlumniEvent;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\CommunicationLog;
use App\Models\Course;
use App\Models\EducationHistory;
use App\Models\EmploymentHistory;
use App\Models\FollowUpTask;
use App\Models\FeeStructure;
use App\Models\Intake;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentChangeRequest;
use App\Models\StudentCourse;
use App\Models\StudentDocument;
use App\Models\StudentNote;
use App\Models\StudyGroup;
use App\Models\AcademicYear;
use App\Models\SchoolClub;
use App\Models\User;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    private function rules(?Student $student = null): array
    {
        return [
            // A. Personal
            'name_english' => ['required', 'string', 'max:255'],
            'name_khmer' => ['nullable', 'string', 'max:255'],
            'name_chinese' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:50', Rule::unique('students', 'phone')->ignore($student?->id)->withoutTrashed()],
            'telegram' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student?->id)->withoutTrashed()],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'preferred_channel' => ['nullable', 'string', 'max:50'],
            'preferred_language' => ['nullable', 'string', 'max:50'],
            'registered_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(Student::STATUS_LABELS))],
            'student_stage' => ['required', Rule::in(array_keys(Student::STAGE_LABELS))],
            'graduation_status' => ['required', Rule::in(array_keys(Student::GRADUATION_STATUS_LABELS))],
            'school_club_ids' => ['nullable', 'array'],
            'school_club_ids.*' => ['integer', 'distinct', 'exists:school_clubs,id'],
            'study_group_ids' => ['nullable', 'array'],
            'study_group_ids.*' => ['integer', 'distinct', 'exists:study_groups,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5000'],
            // Status flags
            'education_status' => ['nullable', Rule::in(array_keys(Student::EDUCATION_STATUS_LABELS))],
            'employment_status' => ['nullable', Rule::in(array_keys(Student::EMPLOYMENT_STATUS_LABELS))],
            // D. Career
            'professional_skills' => ['nullable', 'string', 'max:2000'],
            'technical_skills' => ['nullable', 'string', 'max:2000'],
            'certifications' => ['nullable', 'string', 'max:2000'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'career_goals' => ['nullable', 'string', 'max:2000'],
            'career_notes' => ['nullable', 'string', 'max:5000'],
            // F. Engagement
            'alumni_membership_number' => ['nullable', 'string', 'max:50'],
            'alumni_group' => ['nullable', 'string', 'max:100'],
            'engagement_status' => ['nullable', Rule::in(['engaged', 'semi_engaged', 'not_engaged'])],
            'last_contacted_date' => ['nullable', 'date'],
            'next_followup_date' => ['nullable', 'date'],
            'referral_info' => ['nullable', 'string', 'max:2000'],
            'alumni_feedback' => ['nullable', 'string', 'max:5000'],
            'engagement_notes' => ['nullable', 'string', 'max:5000'],
            // G. System
            'assigned_staff_id' => ['nullable', Rule::exists('users', 'id')],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            // B. Center education (first record)
            'sc_campus_id' => CampusContext::campusRules(false),
            'sc_course_id' => ['nullable', Rule::exists('courses', 'id')],
            'sc_academic_year' => ['nullable', Rule::exists('academic_years', 'id')],
            'sc_intake_id' => ['nullable', Rule::exists('intakes', 'id')],
            'sc_program_id' => ['nullable', Rule::exists('programs', 'id')],
            'sc_semester_id' => ['nullable', Rule::exists('semesters', 'id')],
            'sc_batch_id' => ['nullable', Rule::exists('batches', 'id')],
            'sc_study_group_id' => ['nullable', 'integer', Rule::exists('study_groups', 'id')],
            'sc_term' => ['nullable', 'integer', 'min:1', 'max:12'],
            'sc_registration_number' => ['nullable', 'string', 'max:100'],
            'sc_major' => ['nullable', 'string', 'max:255'],
            'sc_class_group' => ['nullable', 'string', 'max:50'],
            'sc_instructor_name' => ['nullable', 'string', 'max:255'],
            'sc_enrollment_date' => ['nullable', 'date'],
            'sc_course_start_date' => ['nullable', 'date'],
            'sc_course_completion_date' => ['nullable', 'date'],
            'sc_graduation_date' => ['nullable', 'date'],
            'sc_graduation_year' => ['nullable', 'integer', 'min:1980', 'max:2100'],
            'sc_attendance_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sc_grade' => ['nullable', 'string', 'max:20'],
            'sc_scholarship_status' => ['nullable', 'string', 'max:100'],
            'sc_internship_company' => ['nullable', 'string', 'max:255'],
            'sc_graduation_project' => ['nullable', 'string', 'max:255'],
            'sc_withdrawal_reason' => ['nullable', 'string', 'max:2000'],
            'sc_alumni_membership_status' => ['nullable', 'string', 'max:50'],
            'sc_alumni_membership_date' => ['nullable', 'date'],
            'sc_notes' => ['nullable', 'string', 'max:5000'],
            // C. Current education
            'edu_institution' => ['nullable', 'string', 'max:255'],
            'edu_degree_program' => ['nullable', 'string', 'max:255'],
            'edu_major' => ['nullable', 'string', 'max:255'],
            'edu_year_level' => ['nullable', 'string', 'max:50'],
            'edu_start_date' => ['nullable', 'date'],
            'edu_expected_graduation_date' => ['nullable', 'date'],
            'edu_scholarship_info' => ['nullable', 'string', 'max:255'],
            'edu_further_plans' => ['nullable', 'string', 'max:2000'],
            // D. Current employment
            'emp_company' => ['nullable', 'string', 'max:255'],
            'emp_job_title' => ['nullable', 'string', 'max:255'],
            'emp_industry' => ['nullable', 'string', 'max:100'],
            'emp_department' => ['nullable', 'string', 'max:100'],
            'emp_company_address' => ['nullable', 'string', 'max:255'],
            'emp_work_phone' => ['nullable', 'string', 'max:50'],
            'emp_work_email' => ['nullable', 'email', 'max:255'],
            'emp_start_date' => ['nullable', 'date'],
            'emp_salary_range' => ['nullable', 'string', 'max:100'],
            'emp_employment_type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'freelance'])],
        ];
    }

    private function booleans(Request $request): array
    {
        $out = [];
        foreach (['is_currently_studying', 'is_currently_employed', 'is_seeking_employment',
            'interested_jobs', 'interested_internships', 'interested_mentoring', 'interested_trainer',
            'interested_volunteering', 'interested_activities', 'interested_newsletter',
            'interested_job_announcements', 'interested_training', 'sc_certificate_received',
            'sc_internship_participation'] as $field) {
            $out[$field] = $request->boolean($field);
        }
        return $out;
    }

    private function studentFields(Request $request, array $validated): array
    {
        $data = collect($validated)->except([
            'photo', 'cv', 'school_club_ids', 'study_group_ids', 'sc_certificate_received', 'sc_internship_participation',
        ])->all() + [
            'sc_certificate_received' => $request->boolean('sc_certificate_received'),
            'sc_internship_participation' => $request->boolean('sc_internship_participation'),
        ];
        $studentKeys = array_filter(array_keys($this->rules()), fn ($k) => !str_starts_with($k, 'sc_') && !str_starts_with($k, 'edu_') && !str_starts_with($k, 'emp_') && !in_array($k, ['photo', 'cv']));
        $fields = [];
        foreach ($studentKeys as $k) {
            if (array_key_exists($k, $data)) {
                $fields[$k] = $data[$k];
            }
        }
        foreach ($this->booleans($request) as $k => $v) {
            if (!str_starts_with($k, 'sc_')) {
                $fields[$k] = $v;
            }
        }
        return $fields;
    }

    private function saveFiles(Request $request, Student $student): void
    {
        if ($request->hasFile('photo')) {
            if ($student->photo) {
                @unlink(storage_path('app/public/' . $student->photo));
            }
            $student->photo = $request->file('photo')->store('students/photos', 'public');
            $student->save();
        }
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('students/cv', 'public');
            $student->cv_path = $path;
            $student->save();
            StudentDocument::create([
                'student_id' => $student->id,
                'name' => $request->file('cv')->getClientOriginalName(),
                'doc_type' => 'cv',
                'file_path' => $path,
                'file_size' => $request->file('cv')->getSize(),
                'description' => 'CV / Resume uploaded from profile form',
                'is_sensitive' => false,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function saveRelated(Request $request, Student $student, array $validated): void
    {
        // B. Center education record
        $scData = collect($validated)
            ->filter(fn ($v, $k) => str_starts_with($k, 'sc_'))
            ->mapWithKeys(fn ($v, $k) => [substr($k, 3) => $v])
            ->merge($request->only(['sc_certificate_received', 'sc_internship_participation']))
            ->mapWithKeys(fn ($v, $k) => [str_starts_with($k, 'sc_') ? substr($k, 3) : $k => $v])
            ->all();
        $scData['certificate_received'] = $request->boolean('sc_certificate_received');
        $scData['internship_participation'] = $request->boolean('sc_internship_participation');
        unset($scData['academic_year'], $scData['term']);
        $scData['center_name'] = \App\Models\Setting::get('center_name', 'Education Center');

        if (!empty($scData['study_group_id'])) {
            $group = StudyGroup::with('teacher:id,first_name,last_name')->find($scData['study_group_id']);
            if ($group) {
                $scData['class_group'] = $group->name;
                $scData['instructor_name'] = $group->teacher?->fullName() ?? '';
            }
        }

        if (array_filter(array_intersect_key($scData, array_flip([
            'campus_id', 'course_id', 'batch_id', 'intake_id', 'semester_id', 'study_group_id', 'registration_number', 'graduation_year', 'enrollment_date',
        ])))) {
            $existing = $student->activeEnrollment() ?? $student->studentCourses()->first();
            $scData['enrollment_status'] = $this->resolveStatusForRecord(
                $request,
                $existing->enrollment_status ?? null
            );
            if ($existing) {
                $existing->update($scData);
                $this->stampHomeCampus($student, $existing->refresh());
            } else {
                $created = $student->studentCourses()->create($scData);
                $this->createCourseInvoice($created);
                $this->stampHomeCampus($student, $created);
            }
        }

        // C. Current education
        $eduData = collect($request->only([
            'edu_institution', 'edu_degree_program', 'edu_major', 'edu_year_level',
            'edu_start_date', 'edu_expected_graduation_date', 'edu_scholarship_info', 'edu_further_plans',
        ]))->mapWithKeys(fn ($v, $k) => [
            substr($k, 4) === 'further_plans' ? 'further_education_plans' : substr($k, 4) => $v,
        ])->all();
        $eduData['status'] = $request->input('education_status');
        $eduData['is_current'] = true;
        if (array_filter($eduData)) {
            $existing = $student->educationHistories()->where('is_current', true)->first();
            if ($existing) {
                $existing->update($eduData);
            } else {
                $student->educationHistories()->create($eduData);
            }
        }

        // D. Current employment
        $empData = collect($request->only([
            'emp_company', 'emp_job_title', 'emp_industry', 'emp_department', 'emp_company_address',
            'emp_work_phone', 'emp_work_email', 'emp_start_date', 'emp_salary_range', 'emp_employment_type',
        ]))->mapWithKeys(fn ($v, $k) => [substr($k, 4) => $v])->all();
        $empData['employment_status'] = $request->input('employment_status');
        $empData['is_current'] = true;
        if (array_filter($empData)) {
            $existing = $student->employmentHistories()->where('is_current', true)->first();
            if ($existing) {
                $existing->update($empData);
            } else {
                $student->employmentHistories()->create($empData);
            }
        }
    }

    public function index(Request $request)
    {
        $query = Student::query()->with([
            'assignedStaff:id,name',
            'studentCourses.course:id,name',
            'studentCourses.campus:id,name',
            'invoices.items', 'invoices.payments', 'invoices.feeStructure.course',
        ]);

        if (!$request->boolean('archived')) {
            $query->where('record_status', 'active');
        } else {
            $query->where('record_status', 'archived');
        }

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name_english', 'like', "%$q%")
                    ->orWhere('name_khmer', 'like', "%$q%")
                    ->orWhere('student_code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('alumni_membership_number', 'like', "%$q%")
                    ->orWhereHas('studentCourses', function ($w2) use ($q) {
                        $w2->where('registration_number', 'like', "%$q%")
                            ->orWhereHas('course', fn ($w3) => $w3->where('name', 'like', "%$q%"));
                    })
                    ->orWhereHas('employmentHistories', fn ($w2) => $w2->where('company', 'like', "%$q%"));
            });
        }

        $filters = $request->only(['course_id', 'campus_id', 'graduation_year', 'gender', 'employment_status', 'education_status', 'status', 'student_stage', 'graduation_status', 'assigned_staff_id']);

        // Staff land on their active campus unless they chose one explicitly.
        if (($defaultCampus = CampusContext::filterFor($request)) !== null) {
            $filters['campus_id'] = $defaultCampus;
        }

        if (!empty($filters['course_id']) || !empty($filters['campus_id']) || !empty($filters['graduation_year'])) {
            $query->whereHas('studentCourses', function ($w) use ($filters) {
                if (!empty($filters['course_id'])) {
                    $w->where('course_id', $filters['course_id']);
                }
                if (!empty($filters['campus_id'])) {
                    $w->where('campus_id', $filters['campus_id']);
                }
                if (!empty($filters['graduation_year'])) {
                    $w->where('graduation_year', $filters['graduation_year']);
                }
            });
        }
        foreach (['gender', 'employment_status', 'education_status', 'status', 'student_stage', 'graduation_status', 'assigned_staff_id'] as $f) {
            if (!empty($filters[$f])) {
                $query->where($f, $filters[$f]);
            }
        }

        $sort = $request->input('sort', 'name');
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $perPage = (string) $request->input('per_page', '10');
        if (!in_array($perPage, ['10', '25', '50', 'all'], true)) {
            $perPage = '10';
        }
        $sortMap = [
            'name' => 'name_english',
            'registered_date' => 'registered_date',
            'graduation_date' => 'created_at',
            'last_contacted' => 'last_contacted_date',
        ];
        $query->orderBy($sortMap[$sort] ?? 'name_english', $dir);

        $pageSize = $perPage === 'all' ? max($query->count(), 1) : (int) $perPage;
        $students = $query->paginate($pageSize)->withQueryString();

        return view('students.index', [
            'students' => $students,
            'courses' => Course::with(['program:id,name', 'category:id,name_en'])->where('is_active', true)->orderBy('name')->get(),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'staff' => User::whereIn('role_id', \App\Models\Role::whereIn('name', ['admin', 'staff'])->pluck('id'))->where('is_active', true)->orderBy('name')->get(),
            'gradYears' => StudentCourse::whereNotNull('graduation_year')->distinct()->orderByDesc('graduation_year')->pluck('graduation_year'),
            'filters' => $filters + ['q' => $request->input('q'), 'sort' => $sort, 'dir' => $dir, 'archived' => $request->boolean('archived'), 'per_page' => $perPage],
        ]);
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $studentCourse = new StudentCourse();

        return view('students.form', array_merge([
            'student' => new Student(['status' => 'active_alumni', 'student_stage' => 'current_student', 'registered_date' => now()->toDateString()]),
            'studentCourse' => $studentCourse,
            'education' => new EducationHistory(),
            'employment' => new EmploymentHistory(),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'staff' => User::whereIn('role_id', \App\Models\Role::whereIn('name', ['admin', 'staff'])->pluck('id'))->where('is_active', true)->orderBy('name')->get(),
            'schoolClubs' => SchoolClub::where('is_active', true)->orderBy('name')->get(),
            'selectedClubIds' => [],
            'studyGroups' => StudyGroup::with('teacher:id,first_name,last_name')->orderBy('name')->get(['id', 'name', 'code', 'graduation_year', 'teacher_id', 'study_time']),
            'selectedStudyGroupIds' => [],
        ], $this->academicFormData($studentCourse)));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $validated = $request->validate($this->rules());
        $this->validateAcademicSelection($request, $validated);

        $student = DB::transaction(function () use ($request, $validated) {
            $fields = $this->studentFields($request, $validated);
            $fields['student_code'] = Student::generateStudentCode();
            $fields['created_by'] = Auth::id();
            $fields['updated_by'] = Auth::id();
            if (empty($fields['assigned_staff_id'])) {
                $fields['assigned_staff_id'] = Auth::id();
            }
            $student = Student::create($fields);
            $this->saveFiles($request, $student);
            $this->saveRelated($request, $student, $validated);
            $student->schoolClubs()->sync($validated['school_club_ids'] ?? []);
            $student->studyGroups()->sync($validated['study_group_ids'] ?? []);
            $this->attachEnrollmentStudyGroup($student, $validated);
            $this->assignHomeCampus($student);

            return $student;
        });

        AuditLog::record('created', 'student', $student->id, "Created student {$student->name_english} ({$student->student_code})");

        return redirect()->route('students.show', $student)->with('success', "Student {$student->name_english} created successfully with ID {$student->student_code}.");
    }

    public function show(Request $request, Student $student)
    {
        $student->load([
            'assignedStaff:id,name', 'creator:id,name', 'updater:id,name',
            'studentCourses.campus', 'studentCourses.course.category', 'studentCourses.batch', 'studentCourses.intake', 'studentCourses.semester.academicYear',
            'studentCourses.studyGroup.teacher:id,first_name,last_name',
            'studentCourses.invoices.items', 'studentCourses.invoices.payments',
            'invoices.items', 'invoices.payments', 'invoices.feeStructure.course',
            'educationHistories', 'employmentHistories',
            'communicationLogs.user', 'followUpTasks.assignee',
            'documents.uploader', 'notes.author',
            'eventRegistrations.event', 'opportunityApplications.opportunity',
            'schoolClubs', 'studyGroups.campus', 'studyGroups.courseCategory',
            'studyGroups.academicYear', 'studyGroups.program', 'studyGroups.intake',
        ]);
        $pendingChangeRequest = StudentChangeRequest::with('requester:id,name')
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $tab = $request->input('tab', 'overview');
        $allowedTabs = ['overview', 'personal', 'education', 'career', 'communication', 'tasks', 'events', 'documents', 'notes'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'overview';
        }

        $canSensitive = Auth::user()->canViewSensitiveData();
        $upcomingEvents = AlumniEvent::whereIn('status', ['published', 'ongoing'])->where('start_datetime', '>=', now())->orderBy('start_datetime')->get();

        // Already-enrolled academic records must stay in the dropdowns, otherwise editing an
        // enrollment whose course/batch was deactivated silently switches it to another one.
        $enrolledCourseIds = $student->studentCourses->pluck('course_id')->filter()->all();
        $enrolledBatchIds = $student->studentCourses->pluck('batch_id')->filter()->all();

        $enrollmentOptions = $this->academicFormData(new StudentCourse(), $enrolledCourseIds);
        $enrollmentOptions['campuses'] = Campus::where('is_active', true)->orderBy('name')->get();
        if ($enrolledBatchIds) {
            $enrollmentOptions['batches'] = Batch::query()
                ->where(fn ($q) => $q->whereIn('status', ['upcoming', 'ongoing', 'completed'])
                    ->orWhereIn('id', $enrolledBatchIds))
                ->orderBy('name')->get();
        }

        return view('students.show', compact('student', 'tab', 'canSensitive', 'upcomingEvents', 'pendingChangeRequest') + $enrollmentOptions);
    }

    public function edit(Student $student)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        if (!Auth::user()->isAdmin() && $student->assigned_staff_id !== Auth::id()) {
            abort(403, 'You can only edit students assigned to you.');
        }

        $studentCourse = $student->activeEnrollment() ?? $student->studentCourses()->first() ?? new StudentCourse();

        return view('students.form', array_merge([
            'student' => $student,
            'studentCourse' => $studentCourse,
            'education' => $student->educationHistories()->where('is_current', true)->first() ?? new EducationHistory(),
            'employment' => $student->employmentHistories()->where('is_current', true)->first() ?? new EmploymentHistory(),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'staff' => User::whereIn('role_id', \App\Models\Role::whereIn('name', ['admin', 'staff'])->pluck('id'))->where('is_active', true)->orderBy('name')->get(),
            'schoolClubs' => SchoolClub::where('is_active', true)->orderBy('name')->get(),
            'selectedClubIds' => $student->schoolClubs()->pluck('school_clubs.id')->all(),
            'studyGroups' => StudyGroup::with('teacher:id,first_name,last_name')->orderBy('name')->get(['id', 'name', 'code', 'graduation_year', 'teacher_id', 'study_time']),
            'selectedStudyGroupIds' => $student->studyGroups()->pluck('study_groups.id')->all(),
        ], $this->academicFormData($studentCourse)));
    }

    private function academicFormData(StudentCourse $studentCourse, array $extraCourseIds = []): array
    {
        // The current selection stays selectable even after it is deactivated,
        // otherwise the edit form silently drops it and reselects another course.
        $selectedCourseIds = array_filter(array_merge(
            $extraCourseIds,
            $studentCourse->course_id ? [$studentCourse->course_id] : []
        ));
        $selectedIntakeIds = $studentCourse->intake_id ? [$studentCourse->intake_id] : [];
        $selectedSemesterIds = $studentCourse->semester_id ? [$studentCourse->semester_id] : [];
        $selectedBatchIds = $studentCourse->batch_id ? [$studentCourse->batch_id] : [];

        return [
            'courses' => Course::query()
                ->with([
                    'program:id,name',
                    'category:id,name_en,extra_course,academic_year_id,semester_id',
                    'category.academicYear:id,academic_year',
                    'category.semester:id,academic_year_id',
                ])
                ->where(fn ($q) => $q->where('is_active', true)
                    ->orWhereIn('id', $selectedCourseIds))
                ->orderBy('name')->get(),
            'intakes' => Intake::query()
                ->where(fn ($q) => $q->where('is_active', true)
                    ->orWhereIn('id', $selectedIntakeIds))
                ->orderByDesc('academic_year')->orderBy('name')->get(),
            'semesters' => Semester::query()
                ->with('academicYear:id,academic_year')
                ->where(fn ($q) => $q->where('is_active', true)
                    ->orWhereIn('id', $selectedSemesterIds))
                ->orderByDesc('academic_year_id')->orderBy('term')->orderBy('name')->get(),
            'batches' => Batch::query()
                ->where(fn ($q) => $q->whereIn('status', ['upcoming', 'ongoing'])
                    ->orWhereIn('id', $selectedBatchIds))
                ->with(['semester:id,academic_year_id', 'semester.academicYear:id,academic_year', 'intake:id,academic_year'])
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::withTrashed()
                ->with(['campus:id,name', 'courseCategory:id,name_en'])
                ->orderByDesc('from')->get(),
        ];
    }

    public function update(Request $request, Student $student)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        if (!Auth::user()->isAdmin() && $student->assigned_staff_id !== Auth::id()) {
            abort(403, 'You can only edit students assigned to you.');
        }
        $validated = $request->validate($this->rules($student));
        $requestRemarks = $request->validate([
            'request_remarks' => ['nullable', 'string', 'max:2000'],
        ])['request_remarks'] ?? null;
        $this->validateAcademicSelection($request, $validated);

        if (StudentChangeRequest::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return redirect()->route('students.show', $student)
                ->with('error', 'A change request for this student is already awaiting review.');
        }

        $payload = array_merge($validated, $this->booleans($request));
        unset($payload['photo'], $payload['cv']);
        $payload['_original'] = $this->studentChangeRequestOriginalValues($student, $payload);
        $payload['_changes'] = $this->studentChangeRequestDiff($payload, $payload['_original']);
        $changeRequest = StudentChangeRequest::create([
            'student_id' => $student->id,
            'requested_by' => Auth::id(),
            'payload' => $payload,
            'request_remarks' => $requestRemarks,
            'status' => 'pending',
        ]);

        $stagedFiles = [];
        foreach (['photo', 'cv'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $stagedFiles[$field] = [
                    'path' => $file->store("student-change-requests/{$changeRequest->id}", 'local'),
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }
        if ($stagedFiles) {
            $payload['_staged_files'] = $stagedFiles;
            $changeRequest->update(['payload' => $payload]);
        }

        AuditLog::record('submitted', 'student_change_request', $changeRequest->id,
            "Submitted profile changes for {$student->name_english}");
        $adminIds = User::where('is_active', true)
            ->whereHas('role', fn ($query) => $query->whereIn('name', ['super_admin', 'admin']))
            ->pluck('id');
        foreach ($adminIds as $adminId) {
            Notification::create([
                'user_id' => $adminId,
                'type' => 'student_update',
                'title' => 'Student changes need approval',
                'message' => Auth::user()->name . " submitted profile changes for {$student->name_english}.",
                'link' => route('student-change-requests.index'),
            ]);
        }

        return redirect()->route('students.show', $student)
            ->with('success', 'Student changes were submitted and are waiting for administrator approval.');
    }

    private function applyStudentUpdate(Request $request, Student $student, array $validated): void
    {
        DB::transaction(function () use ($request, $student, $validated) {
            $fields = $this->studentFields($request, $validated);
            $fields['updated_by'] = Auth::id();
            $student->update($fields);
            $this->saveFiles($request, $student);
            $this->saveRelated($request, $student, $validated);
            $student->schoolClubs()->sync($validated['school_club_ids'] ?? []);
            $student->studyGroups()->sync($validated['study_group_ids'] ?? []);
            $this->attachEnrollmentStudyGroup($student, $validated);
        });
    }

    private function attachEnrollmentStudyGroup(Student $student, array $validated): void
    {
        if (!empty($validated['sc_study_group_id'])) {
            $student->studyGroups()->syncWithoutDetaching([(int) $validated['sc_study_group_id']]);
        }
    }

    private function studentChangeRequestOriginalValues(Student $student, array $payload): array
    {
        $enrollment = $student->activeEnrollment() ?? $student->studentCourses()->first();
        $education = $student->educationHistories()->where('is_current', true)->first();
        $employment = $student->employmentHistories()->where('is_current', true)->first();
        $original = [];

        foreach ($payload as $key => $value) {
            if (array_key_exists($key, $student->getAttributes())) {
                $original[$key] = $student->getAttribute($key);
            } elseif (str_starts_with($key, 'sc_') && $enrollment) {
                $attribute = substr($key, 3);
                $original[$key] = in_array($attribute, ['academic_year', 'term'], true)
                    ? null
                    : $enrollment->getAttribute($attribute);
            } elseif (str_starts_with($key, 'edu_') && $education) {
                $attribute = substr($key, 4);
                $original[$key] = $education->getAttribute($attribute === 'further_plans' ? 'further_education_plans' : $attribute);
            } elseif (str_starts_with($key, 'emp_') && $employment) {
                $original[$key] = $employment->getAttribute(substr($key, 4));
            } elseif ($key === 'school_club_ids') {
                $original[$key] = $student->schoolClubs()->pluck('school_clubs.id')->all();
            } elseif ($key === 'study_group_ids') {
                $original[$key] = $student->studyGroups()->pluck('study_groups.id')->all();
            } else {
                $original[$key] = null;
            }
        }

        return $original;
    }

    private function studentChangeRequestDiff(array $proposed, array $original): array
    {
        $normalize = static function ($value): mixed {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            if (is_array($value)) {
                $value = array_map(static fn ($item) => is_null($item) ? '' : (string) $item, $value);
                sort($value);
                return $value;
            }
            if ($value === null || $value === '') {
                return '';
            }
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }
            if (is_numeric($value)) {
                return (string) (0 + $value);
            }

            return trim((string) $value);
        };

        $changes = [];
        foreach ($proposed as $field => $newValue) {
            if (str_starts_with($field, '_')) {
                continue;
            }
            $oldValue = $original[$field] ?? null;
            if ($normalize($oldValue) !== $normalize($newValue)) {
                $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        return $changes;
    }

    public function changeRequests()
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $status = request('status', 'all');
        abort_unless(in_array($status, ['all', 'pending', 'approved', 'rejected'], true), 404);

        $requests = StudentChangeRequest::with(['student:id,name_english,student_code', 'requester:id,name', 'reviewer:id,name'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 WHEN 'rejected' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('students.change-requests', compact('requests', 'status'));
    }

    public function reviewChangeRequest(Request $request, StudentChangeRequest $changeRequest)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'This change request has already been reviewed.');
        }

        if ($data['decision'] === 'reject') {
            $this->deleteStagedFiles($changeRequest->payload ?? []);
            $changeRequest->update([
                'status' => 'rejected', 'reviewed_by' => Auth::id(),
                'reviewed_at' => now(), 'review_note' => $data['review_note'] ?? null,
            ]);
            AuditLog::record('rejected', 'student_change_request', $changeRequest->id,
                "Rejected profile changes for {$changeRequest->student?->name_english}");

            return back()->with('success', 'Student changes were rejected; the existing student profile remains unchanged.');
        }

        DB::transaction(function () use ($changeRequest, $data) {
            $changeRequest = StudentChangeRequest::whereKey($changeRequest->id)->lockForUpdate()->firstOrFail();
            abort_unless($changeRequest->status === 'pending', 409, 'This change request has already been reviewed.');
            $student = Student::findOrFail($changeRequest->student_id);
            $payload = $changeRequest->payload ?? [];
            $stagedFiles = $payload['_staged_files'] ?? [];
            unset($payload['_staged_files']);

            $formRequest = Request::create('/', 'POST', $payload);
            $validated = Validator::make($payload, $this->rules($student))->validate();
            $validated = array_merge($validated, $this->booleans($formRequest));
            $this->validateAcademicSelection($formRequest, $validated);
            $this->applyStudentUpdate($formRequest, $student, $validated);
            $this->applyStagedFiles($student, $stagedFiles);

            $changeRequest->update([
                'status' => 'approved', 'reviewed_by' => Auth::id(),
                'reviewed_at' => now(), 'review_note' => $data['review_note'] ?? null,
            ]);
            AuditLog::record('approved', 'student_change_request', $changeRequest->id,
                "Approved profile changes for {$student->name_english}");
        });

        return back()->with('success', 'Student changes approved and applied.');
    }

    private function applyStagedFiles(Student $student, array $files): void
    {
        foreach (['photo', 'cv'] as $field) {
            if (empty($files[$field]['path'])) {
                continue;
            }
            $stagedPath = $files[$field]['path'];
            abort_unless(Storage::disk('local')->exists($stagedPath), 422, 'A submitted file is no longer available. Ask the staff member to submit the change again.');
            $targetDirectory = $field === 'photo' ? 'students/photos' : 'students/cv';
            $targetPath = $targetDirectory . '/' . basename($stagedPath);
            Storage::disk('public')->put($targetPath, Storage::disk('local')->get($stagedPath));

            if ($field === 'photo') {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $student->update(['photo' => $targetPath, 'updated_by' => Auth::id()]);
            } else {
                $student->update(['cv_path' => $targetPath, 'updated_by' => Auth::id()]);
                StudentDocument::create([
                    'student_id' => $student->id,
                    'name' => $files[$field]['original_name'] ?? basename($targetPath),
                    'doc_type' => 'cv',
                    'file_path' => $targetPath,
                    'file_size' => $files[$field]['size'] ?? Storage::disk('public')->size($targetPath),
                    'description' => 'CV / Resume uploaded from an approved profile change',
                    'is_sensitive' => false,
                    'uploaded_by' => Auth::id(),
                ]);
            }
            Storage::disk('local')->delete($stagedPath);
        }
    }

    private function deleteStagedFiles(array $payload): void
    {
        foreach (($payload['_staged_files'] ?? []) as $file) {
            if (!empty($file['path'])) {
                Storage::disk('local')->delete($file['path']);
            }
        }
    }

    private function validateAcademicSelection(Request $request, array $validated): void
    {
        $semester = !empty($validated['sc_semester_id'])
            ? Semester::find($validated['sc_semester_id'])
            : null;

        if ($semester && $semester->term !== null && (int) ($validated['sc_term'] ?? 0) !== (int) $semester->term) {
            throw ValidationException::withMessages(['sc_term' => 'The selected term does not match the semester.']);
        }

        if ($semester && $semester->academic_year_id !== null && (int) ($validated['sc_academic_year'] ?? 0) !== (int) $semester->academic_year_id) {
            throw ValidationException::withMessages(['sc_academic_year' => 'The semester does not belong to the selected academic year.']);
        }

        if (empty($validated['sc_batch_id'])) {
            return;
        }

        $batch = Batch::findOrFail($validated['sc_batch_id']);
        foreach ([
            'sc_course_id' => 'course_id',
            'sc_campus_id' => 'campus_id',
            'sc_semester_id' => 'semester_id',
            'sc_intake_id' => 'intake_id',
        ] as $requestField => $batchField) {
            $selected = $validated[$requestField] ?? null;
            if ($batch->{$batchField} && (int) $selected !== (int) $batch->{$batchField}) {
                throw ValidationException::withMessages([
                    $requestField => 'The selected academic options do not match the selected batch.',
                ]);
            }
        }
    }

    public function destroy(Student $student)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        if ($request_archived = $student->record_status === 'archived') {
            $student->delete();
            AuditLog::record('deleted', 'student', $student->id, "Deleted student {$student->name_english}");
            return redirect()->route('students.index', ['archived' => 1])->with('success', 'Student record permanently deleted.');
        }
        $student->update(['record_status' => 'archived']);
        AuditLog::record('archived', 'student', $student->id, "Archived student {$student->name_english}");
        return redirect()->route('students.index')->with('success', "Student {$student->name_english} archived.");
    }

    public function restore(Student $student)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        if ($student->trashed()) {
            $student->restore();
        }
        $student->update(['record_status' => 'active']);
        AuditLog::record('restored', 'student', $student->id, "Restored student {$student->name_english}");
        return redirect()->route('students.index')->with('success', "Student {$student->name_english} restored.");
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Student::query()->with(['studentCourses.course', 'studentCourses.campus', 'assignedStaff:id,name', 'employmentHistories']);
        $this->applyExportFilters($query, $request);

        $columns = ['Student ID', 'English Name', 'Khmer Name', 'Gender', 'Phone', 'Email', 'Telegram',
            'Province', 'Status', 'Lifecycle Stage', 'Graduation Status', 'Course', 'Campus', 'Graduation Year', 'Education Status',
            'Employment Status', 'Current Company', 'Job Title', 'Engagement Status',
            'Last Contacted', 'Next Follow-up', 'Assigned Staff', 'Registered Date'];

        AuditLog::record('export', 'student', null, 'Exported students to CSV');

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            $query->orderBy('name_english')->chunk(200, function ($students) use ($out) {
                foreach ($students as $s) {
                    $sc = $s->studentCourses->sortByDesc('graduation_year')->first();
                    $emp = $s->employmentHistories->firstWhere('is_current', true);
                    fputcsv($out, [
                        $s->student_code, $s->name_english, $s->name_khmer, $s->gender, $s->phone,
                        $s->email, $s->telegram, $s->province,
                        Student::STATUS_LABELS[$s->status] ?? $s->status,
                        Student::STAGE_LABELS[$s->student_stage] ?? $s->student_stage,
                        Student::GRADUATION_STATUS_LABELS[$s->graduation_status] ?? $s->graduation_status,
                        $sc?->course?->name, $sc?->campus?->name, $sc?->graduation_year,
                        Student::EDUCATION_STATUS_LABELS[$s->education_status] ?? $s->education_status,
                        Student::EMPLOYMENT_STATUS_LABELS[$s->employment_status] ?? $s->employment_status,
                        $emp?->company, $emp?->job_title, $s->engagement_status,
                        $s->last_contacted_date?->toDateString(), $s->next_followup_date?->toDateString(),
                        $s->assignedStaff?->name, $s->registered_date?->toDateString(),
                    ]);
                }
            });
            fclose($out);
        }, 'students-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function applyExportFilters($query, Request $request): void
    {
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name_english', 'like', "%$q%")->orWhere('student_code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")->orWhere('email', 'like', "%$q%");
            });
        }
        foreach (['gender', 'employment_status', 'education_status', 'status', 'student_stage', 'graduation_status', 'assigned_staff_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }
        // Keep the export in step with what the list screen shows staff.
        $defaultCampus = CampusContext::filterFor($request);
        if ($defaultCampus !== null) {
            $request->merge(['campus_id' => $defaultCampus]);
        }

        if ($request->filled('course_id') || $request->filled('campus_id') || $request->filled('graduation_year')) {
            $query->whereHas('studentCourses', function ($w) use ($request) {
                if ($request->filled('course_id')) {
                    $w->where('course_id', $request->input('course_id'));
                }
                if ($request->filled('campus_id')) {
                    $w->where('campus_id', $request->input('campus_id'));
                }
                if ($request->filled('graduation_year')) {
                    $w->where('graduation_year', $request->input('graduation_year'));
                }
            });
        }
        if (!$request->boolean('archived')) {
            $query->where('record_status', 'active');
        }
    }

    public function importForm()
    {
        return view('students.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('file');
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return back()->with('error', 'Could not read the uploaded file.');
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'The file is empty or has no header row.');
        }
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }
            $rows[] = array_combine($header, array_pad($row, count($header), null));
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'No data rows found in the file.');
        }

        $imported = 0; $skipped = 0; $errors = [];
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $name = trim((string) ($row['english name'] ?? $row['name'] ?? $row['name_english'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));
            if ($name === '' || $phone === '') {
                $errors[] = "Row $line: English name and phone are required.";
                $skipped++;
                continue;
            }
            $email = trim((string) ($row['email'] ?? ''));
            if (Student::withTrashed()->where('phone', $phone)->orWhere(fn ($q) => $email && $q->where('email', $email))->exists()) {
                $errors[] = "Row $line: duplicate phone/email ($name) — skipped.";
                $skipped++;
                continue;
            }
            $course = Course::where('name', 'like', trim((string) ($row['course'] ?? '')))->first();
            $campus = Campus::where('name', 'like', trim((string) ($row['campus'] ?? '')))->first();
            $gradYear = (int) ($row['graduation year'] ?? $row['graduation_year'] ?? 0) ?: null;

            DB::transaction(function () use ($row, $name, $phone, $email, $course, $campus, $gradYear, &$imported) {
                $student = Student::create([
                    'student_code' => Student::generateStudentCode(),
                    'name_english' => $name,
                    'name_khmer' => trim((string) ($row['khmer name'] ?? '')) ?: null,
                    'gender' => in_array(strtolower(trim((string) ($row['gender'] ?? ''))), ['male', 'female', 'other']) ? strtolower(trim($row['gender'])) : null,
                    'phone' => $phone,
                    'email' => $email ?: null,
                    'telegram' => trim((string) ($row['telegram'] ?? '')) ?: null,
                    'province' => trim((string) ($row['province'] ?? '')) ?: null,
                    'status' => 'active_alumni',
                    'registered_date' => now(),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'assigned_staff_id' => Auth::id(),
                ]);
                if ($course || $campus || $gradYear) {
                    $enrollment = $student->studentCourses()->create([
                        'course_id' => $course?->id,
                        'campus_id' => $campus?->id,
                        'graduation_year' => $gradYear,
                        'center_name' => \App\Models\Setting::get('center_name', 'Education Center'),
                    ]);
                    $this->stampHomeCampus($student, $enrollment);
                }
                $this->assignHomeCampus($student);
                $imported++;
            });
        }

        AuditLog::record('import', 'student', null, "Imported $imported students from CSV ($skipped skipped)");

        $msg = "Import complete: $imported imported, $skipped skipped.";
        if ($errors) {
            return redirect()->route('students.index')->with('success', $msg)->with('error', implode(' ', array_slice($errors, 0, 10)) . (count($errors) > 10 ? ' ...' : ''));
        }
        return redirect()->route('students.index')->with('success', $msg);
    }

    public function duplicates()
    {
        $dupEmails = Student::query()->select('email')->whereNotNull('email')->groupBy('email')->havingRaw('COUNT(*) > 1')->pluck('email');
        $dupPhones = Student::query()->select('phone')->whereNotNull('phone')->groupBy('phone')->havingRaw('COUNT(*) > 1')->pluck('phone');

        $groups = collect();
        foreach ($dupEmails as $email) {
            $groups->push(['type' => 'Email: ' . $email, 'students' => Student::where('email', $email)->orderBy('id')->get()]);
        }
        foreach ($dupPhones as $phone) {
            $students = Student::where('phone', $phone)->orderBy('id')->get();
            if (!$groups->contains(fn ($g) => $g['students']->pluck('id')->toArray() === $students->pluck('id')->toArray())) {
                $groups->push(['type' => 'Phone: ' . $phone, 'students' => $students]);
            }
        }

        return view('students.duplicates', ['groups' => $groups]);
    }

    public function merge(Request $request)
    {
        $data = $request->validate([
            'keep_id' => ['required', 'integer', 'exists:students,id'],
            'discard_id' => ['required', 'integer', 'exists:students,id', 'different:keep_id'],
        ]);

        DB::transaction(function () use ($data) {
            $keep = Student::findOrFail($data['keep_id']);
            $discard = Student::findOrFail($data['discard_id']);

            foreach ([
                'studentCourses' => 'student_id', 'educationHistories' => 'student_id',
                'employmentHistories' => 'student_id', 'communicationLogs' => 'student_id',
                'followUpTasks' => 'student_id', 'documents' => 'student_id', 'notes' => 'student_id',
                'eventRegistrations' => 'student_id', 'opportunityApplications' => 'student_id',
            ] as $relation => $fk) {
                foreach ($discard->{$relation} as $record) {
                    // skip if a unique record already exists for the kept student
                    try {
                        $record->update([$fk => $keep->id]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        $record->delete();
                    }
                }
            }

            // Fill empty fields on kept record from discarded one
            $fill = [];
            foreach ($discard->getAttributes() as $k => $v) {
                if (!in_array($k, ['id', 'student_code', 'created_at', 'updated_at', 'deleted_at', 'record_status', 'created_by']) && empty($keep->{$k}) && !is_null($v)) {
                    $fill[$k] = $v;
                }
            }
            if ($fill) {
                $keep->forceFill($fill)->save();
            }

            $discard->delete();
            AuditLog::record('merged', 'student', $keep->id, "Merged student #{$discard->id} ({$discard->name_english}) into #{$keep->id} ({$keep->name_english})");
        });

        return redirect()->route('students.duplicates')->with('success', 'Duplicate records merged successfully.');
    }

    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'staff_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        Student::whereIn('id', $data['student_ids'])->update(['assigned_staff_id' => $data['staff_id'], 'updated_by' => Auth::id()]);
        AuditLog::record('bulk_assigned', 'student', null, 'Bulk assigned ' . count($data['student_ids']) . ' students to staff #' . $data['staff_id']);

        return back()->with('success', count($data['student_ids']) . ' student(s) assigned successfully.');
    }

    public function profileExport(Student $student)
    {
        $student->load(['studentCourses.campus', 'studentCourses.course.category', 'studentCourses.batch', 'studentCourses.intake', 'studentCourses.semester.academicYear', 'studentCourses.studyGroup', 'educationHistories',
            'employmentHistories', 'communicationLogs', 'followUpTasks', 'documents', 'notes',
            'eventRegistrations.event', 'assignedStaff:id,name']);

        AuditLog::record('export', 'student', $student->id, "Exported profile of {$student->name_english}");

        return view('students.profile-print', ['student' => $student]);
    }

    public function card(Student $student)
    {
        $student->load(['studentCourses.campus', 'studentCourses.course']);

        AuditLog::record('export', 'student', $student->id, "Generated student card for {$student->name_english}");

        return view('students.card', compact('student'));
    }

    public function certificate(Student $student)
    {
        abort_unless($student->graduation_status === 'graduated', 404, 'Certificates are available only for graduated students.');
        $student->load(['studentCourses.course', 'studentCourses.campus']);
        $course = $student->studentCourses->sortByDesc('graduation_date')->first();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('students.certificate', compact('student', 'course'))
            ->setPaper('a4', 'landscape')
            ->stream('certificate-' . $student->student_code . '.pdf');
    }

    private function authorizeEnrollmentManage(Student $student): void
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        if (!Auth::user()->isAdmin() && $student->assigned_staff_id !== Auth::id()) {
            abort(403, 'You can only manage courses for students assigned to you.');
        }
    }

    /**
     * students.campus_id drives the campus boundary for every student-owned record, so
     * the first enrollment that names a campus also pins the student to it.
     */
    private function stampHomeCampus(Student $student, StudentCourse $enrollment): void
    {
        $campusId = $enrollment->campus_id
            ?? $enrollment->batch?->campus_id
            ?? $enrollment->studyGroup?->campus_id;

        if ($campusId !== null && $student->campus_id === null) {
            $student->forceFill(['campus_id' => $campusId])->save();
        }
    }

    /**
     * A campus account would otherwise lose the student it just created, because a form
     * submitted without an enrollment campus leaves students.campus_id empty.
     */
    private function assignHomeCampus(Student $student): void
    {
        $visible = CampusContext::visibleIds();
        $user = Auth::user();

        if ($student->campus_id === null && $visible !== null && $visible !== [] && $user) {
            $student->forceFill(['campus_id' => CampusContext::current($user) ?? $visible[0]])->save();
        }
    }

    private function enrollmentRules(): array
    {
        return [
            'type' => ['nullable', Rule::in(['main', 'supplementary'])],
            'course_id' => ['required', Rule::exists('courses', 'id')],
            'campus_id' => CampusContext::campusRules(false),
            'batch_id' => ['nullable', Rule::exists('batches', 'id')],
            'intake_id' => ['nullable', Rule::exists('intakes', 'id')],
            'semester_id' => ['nullable', Rule::exists('semesters', 'id')],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'enrollment_status' => ['nullable', Rule::in(array_keys(StudentCourse::STATUS_LABELS))],
            'enrollment_date' => ['nullable', 'date'],
            'course_start_date' => ['nullable', 'date'],
            'course_completion_date' => ['nullable', 'date'],
            'graduation_date' => ['nullable', 'date'],
            'grade' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function resolveEnrollmentStatus(Request $request): string
    {
        $status = $request->input('enrollment_status');
        if (in_array($status, array_keys(StudentCourse::STATUS_LABELS), true)) {
            return $status;
        }
        if ($request->filled('course_completion_date') || $request->filled('graduation_date')) {
            return 'completed';
        }
        return 'in_progress';
    }

    /**
     * Status for the register/edit student form, whose center-education fields are sc_-prefixed
     * and have no explicit status control. Keeps a manually-set withdrawn/deferred remark intact
     * and otherwise derives Current/Completed from the completion/graduation dates.
     */
    private function resolveStatusForRecord(Request $request, ?string $existingStatus): string
    {
        $explicit = $request->input('sc_enrollment_status');
        if (in_array($explicit, array_keys(StudentCourse::STATUS_LABELS), true)) {
            return $explicit;
        }
        if (in_array($existingStatus, ['withdrawn', 'deferred'], true)) {
            return $existingStatus;
        }
        if ($request->filled('sc_course_completion_date') || $request->filled('sc_graduation_date')) {
            return 'completed';
        }
        return 'in_progress';
    }

    private function assertNoActiveDuplicate(Student $student, int $courseId, ?int $exceptId = null): void
    {
        $exists = $student->studentCourses()
            ->where('course_id', $courseId)
            ->whereIn('enrollment_status', ['in_progress', 'deferred'])
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'course_id' => 'This student already has an active enrollment for this course.',
            ]);
        }
    }

    private function assertCourseMatchesType(Course $course, string $type): void
    {
        if ($type === 'supplementary' && !$course->isExtraCourse()) {
            throw ValidationException::withMessages([
                'course_id' => "“{$course->name}” is not a supplementary course. Flag its category as an extra course in Course Categories first.",
            ]);
        }
        if ($type === 'main' && $course->isExtraCourse()) {
            throw ValidationException::withMessages([
                'course_id' => "“{$course->name}” is a supplementary course. Use “Add Supplementary Course” instead.",
            ]);
        }
    }

    /** Build a draft invoice from the course's active fee structures. Returns null if none defined. */
    private function createCourseInvoice(StudentCourse $enrollment): ?Invoice
    {
        if (!$enrollment->course_id) {
            return null;
        }
        $fees = FeeStructure::query()
            ->where('course_id', $enrollment->course_id)
            ->where('is_active', true)
            ->when($enrollment->campus_id, fn ($q) => $q->where(fn ($w) => $w->whereNull('campus_id')->orWhere('campus_id', $enrollment->campus_id)))
            ->get();

        if ($fees->isEmpty()) {
            return null;
        }

        $invoice = new Invoice([
            'student_id' => $enrollment->student_id,
            'student_course_id' => $enrollment->id,
            'issue_date' => now()->toDateString(),
            'status' => 'draft',
            'notes' => 'Auto-generated for course enrollment',
        ]);
        $invoice->invoice_number = Invoice::nextNumber();
        $invoice->created_by = Auth::id();
        $invoice->save();

        foreach ($fees as $fee) {
            $invoice->items()->create([
                'description' => $fee->name,
                'quantity' => 1,
                'unit_price' => $fee->amount,
                'amount' => $fee->amount,
            ]);
        }

        return $invoice;
    }

    public function enrollmentsStore(Request $request, Student $student)
    {
        $this->authorizeEnrollmentManage($student);
        $validated = $request->validate($this->enrollmentRules());
        $type = $validated['type'] ?? 'main';
        $course = Course::findOrFail($validated['course_id']);
        $this->assertCourseMatchesType($course, $type);
        $this->assertNoActiveDuplicate($student, (int) $validated['course_id']);
        unset($validated['type']);

        [$enrollment, $invoice] = DB::transaction(function () use ($student, $validated, $request) {
            $enrollment = $student->studentCourses()->create([
                ...$validated,
                'enrollment_status' => $this->resolveEnrollmentStatus($request),
                'center_name' => \App\Models\Setting::get('center_name', 'Education Center'),
            ]);
            $this->stampHomeCampus($student, $enrollment);

            return [$enrollment, $this->createCourseInvoice($enrollment)];
        });

        AuditLog::record('updated', 'student', $student->id, "Enrolled {$student->name_english} in course {$enrollment->course?->name}");

        $message = $type === 'supplementary'
            ? "Supplementary course {$enrollment->course?->name} added for {$student->name_english}."
            : "Course {$enrollment->course?->name} added for {$student->name_english}.";
        if ($invoice) {
            $message .= " Draft invoice {$invoice->invoice_number} created (" . number_format($invoice->total(), 2) . ').';
        } else {
            $message .= ' No fee structure is defined for this course, so no invoice was created.';
        }

        return redirect()->route('students.show', [$student, 'tab' => 'education'])->with('success', $message);
    }

    public function enrollmentsUpdate(Request $request, Student $student, StudentCourse $enrollment)
    {
        $this->authorizeEnrollmentManage($student);
        abort_unless($enrollment->student_id === $student->id, 404);
        $validated = $request->validate($this->enrollmentRules());
        $course = Course::findOrFail($validated['course_id']);
        $this->assertCourseMatchesType($course, $validated['type'] ?? ($course->isExtraCourse() ? 'supplementary' : 'main'));
        $this->assertNoActiveDuplicate($student, (int) $validated['course_id'], $enrollment->id);
        unset($validated['type']);

        $enrollment->update([
            ...$validated,
            'enrollment_status' => $this->resolveEnrollmentStatus($request),
        ]);

        AuditLog::record('updated', 'student', $student->id, "Updated course enrollment {$enrollment->course?->name} for {$student->name_english}");

        return redirect()->route('students.show', [$student, 'tab' => 'education'])
            ->with('success', "Course {$enrollment->course?->name} updated.");
    }

    public function enrollmentsDestroy(Student $student, StudentCourse $enrollment)
    {
        $this->authorizeEnrollmentManage($student);
        abort_unless($enrollment->student_id === $student->id, 404);

        $courseName = $enrollment->course?->name ?? 'course';
        $enrollment->delete();

        AuditLog::record('updated', 'student', $student->id, "Removed course enrollment {$courseName} for {$student->name_english}");

        return redirect()->route('students.show', [$student, 'tab' => 'education'])
            ->with('success', "Course {$courseName} removed.");
    }
}
