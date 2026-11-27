<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AlumniEventController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DeviceAttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\ClassAttendanceController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\OutreachCampaignController;
use App\Http\Controllers\OutreachLeadController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SchoolClubController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentMovementController;
use App\Http\Controllers\StudentNoteController;
use App\Http\Controllers\StudyGroupController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentFaceController;
use App\Http\Controllers\EmployeeFaceController;
use App\Http\Controllers\FaceController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.attempt');
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('campus/switch', [CampusController::class, 'switchCampus'])->name('campus.switch');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::resource('roles', RoleController::class)->middleware('role:super_admin');
    // Students — viewer: read only; staff+: manage
    Route::get('students/export', [StudentController::class, 'export'])->name('students.export');
    Route::get('students/pipeline', [PipelineController::class, 'index'])->name('students.pipeline');
    Route::patch('students/{student}/pipeline-stage', [PipelineController::class, 'updateStage'])->middleware('role:super_admin,admin,staff')->name('students.pipeline-stage');
    Route::get('students/duplicates', [StudentController::class, 'duplicates'])->middleware('role:super_admin,admin,staff')->name('students.duplicates');
    Route::post('students/merge', [StudentController::class, 'merge'])->middleware('role:super_admin,admin,staff')->name('students.merge');
    Route::get('students/import', [StudentController::class, 'importForm'])->middleware('role:super_admin,admin,staff')->name('students.import.form');
    Route::post('students/import', [StudentController::class, 'import'])->middleware('role:super_admin,admin,staff')->name('students.import');
    Route::post('students/bulk-assign', [StudentController::class, 'bulkAssign'])->middleware('role:super_admin,admin')->name('students.bulk-assign');
    Route::post('students/bulk-campus', [StudentController::class, 'bulkCampus'])->middleware('role:super_admin,admin')->name('students.bulk-campus');
    Route::get('student-change-requests', [StudentController::class, 'changeRequests'])->middleware('role:super_admin,admin')->name('student-change-requests.index');
    Route::patch('student-change-requests/{changeRequest}', [StudentController::class, 'reviewChangeRequest'])->middleware('role:super_admin,admin')->name('student-change-requests.review');
    Route::post('students/{student}/restore', [StudentController::class, 'restore'])->middleware('role:super_admin,admin,staff')->name('students.restore');
    Route::get('students/{student}/profile-export', [StudentController::class, 'profileExport'])->name('students.profile-export');
    Route::get('students/{student}/card', [StudentController::class, 'card'])->name('students.card');
    Route::get('students/{student}/certificate', [StudentController::class, 'certificate'])->name('students.certificate');
    Route::resource('students', StudentController::class)->parameters(['students' => 'student']);

    Route::middleware('role:super_admin,admin,staff')->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/face', [AttendanceController::class, 'index'])->name('face');
        Route::get('/finger', [AttendanceController::class, 'index'])->name('finger');
        Route::get('class', [ClassAttendanceController::class, 'index'])->name('class');
        Route::post('class', [ClassAttendanceController::class, 'store'])->name('class.store');
        Route::get('export', [AttendanceController::class, 'export'])->name('export');
        Route::post('/scan', [AttendanceController::class, 'scan'])->name('scan');
        Route::get('/device-template', [DeviceAttendanceController::class, 'template'])->name('device-template');
        Route::post('/device-import', [DeviceAttendanceController::class, 'import'])->name('device-import');
    });

    // Study groups and batch graduation actions (student-management roles).
    Route::middleware('role:super_admin,admin,staff')->group(function () {
        Route::resource('study-groups', StudyGroupController::class)->parameters(['study-groups' => 'studyGroup']);
        Route::post('study-groups/{studyGroup}/members', [StudyGroupController::class, 'addStudent'])->name('study-groups.members.store');
        Route::delete('study-groups/{studyGroup}/members/{student}', [StudyGroupController::class, 'removeStudent'])->name('study-groups.members.destroy');
        Route::post('study-groups/{studyGroup}/graduate', [StudyGroupController::class, 'graduateSelected'])->name('study-groups.graduate');
    });

    // Student sub-resources
    Route::post('students/{student}/enrollments', [StudentController::class, 'enrollmentsStore'])->middleware('role:super_admin,admin,staff')->name('students.enrollments.store');
    Route::put('students/{student}/enrollments/{enrollment}', [StudentController::class, 'enrollmentsUpdate'])->middleware('role:super_admin,admin,staff')->name('students.enrollments.update');
    Route::delete('students/{student}/enrollments/{enrollment}', [StudentController::class, 'enrollmentsDestroy'])->middleware('role:super_admin,admin,staff')->name('students.enrollments.destroy');
    Route::post('students/{student}/notes', [StudentNoteController::class, 'store'])->middleware('role:super_admin,admin,staff')->name('students.notes.store');
    Route::delete('notes/{note}', [StudentNoteController::class, 'destroy'])->middleware('role:super_admin,admin,staff')->name('notes.destroy');
    Route::post('students/{student}/documents', [DocumentController::class, 'store'])->middleware('role:super_admin,admin,staff')->name('students.documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->middleware('role:super_admin,admin,staff')->name('documents.destroy');

    // Communications
    Route::resource('communications', CommunicationController::class)->except(['create', 'show'])
        ->parameters(['communications' => 'communication']);
    Route::post('students/{student}/communications', [CommunicationController::class, 'store'])
        ->middleware('role:super_admin,admin,staff')->name('students.communications.store');

    // Follow-up tasks
    Route::get('follow-ups/calendar', [FollowUpController::class, 'calendar'])->name('follow-ups.calendar');
    Route::patch('follow-ups/{task}/status', [FollowUpController::class, 'updateStatus'])->middleware('role:super_admin,admin,staff')->name('follow-ups.status');
    Route::resource('follow-ups', FollowUpController::class)->except(['create', 'show'])->parameters(['follow-ups' => 'task']);

    // Alumni events
    Route::resource('events', AlumniEventController::class)->parameters(['events' => 'event']);
    Route::post('events/{event}/registrations', [EventRegistrationController::class, 'store'])->middleware('role:super_admin,admin,staff')->name('events.registrations.store');
    Route::post('events/registrations/{registration}/email', [EventRegistrationController::class, 'emailConfirmation'])->middleware('role:super_admin,admin,staff')->name('events.registrations.email');
    Route::patch('events/registrations/{registration}', [EventRegistrationController::class, 'update'])->middleware('role:super_admin,admin,staff')->name('events.registrations.update');
    Route::delete('events/registrations/{registration}', [EventRegistrationController::class, 'destroy'])->middleware('role:super_admin,admin,staff')->name('events.registrations.destroy');

    // Outreach campaigns (contact pipeline for events, opportunities, surveys...)
    Route::middleware('role:super_admin,admin,staff')->group(function () {
        Route::get('outreach/{campaign}/board', [OutreachCampaignController::class, 'board'])->name('outreach.board');
        Route::post('outreach/{campaign}/build-audience', [OutreachCampaignController::class, 'buildAudience'])->name('outreach.build-audience');
        Route::post('outreach/{campaign}/leads', [OutreachCampaignController::class, 'addLead'])->name('outreach.leads.store');
        Route::post('outreach/{campaign}/leads-bulk', [OutreachCampaignController::class, 'addLeads'])->name('outreach.leads.bulk');
        Route::patch('outreach/leads/{lead}/stage', [OutreachLeadController::class, 'updateStage'])->name('outreach.leads.stage');
        Route::post('outreach/leads/{lead}/log-contact', [OutreachLeadController::class, 'logContact'])->name('outreach.leads.contact');
        Route::delete('outreach/leads/{lead}', [OutreachLeadController::class, 'destroy'])->name('outreach.leads.destroy');
        Route::resource('outreach', OutreachCampaignController::class)->except(['show'])->parameters(['outreach' => 'campaign']);
    });

    // Opportunities
    Route::get('opportunities/{opportunity}/match', [OpportunityController::class, 'match'])->middleware('role:super_admin,admin,staff')->name('opportunities.match');
    Route::post('opportunities/{opportunity}/applications', [OpportunityController::class, 'storeApplication'])->middleware('role:super_admin,admin,staff')->name('opportunities.applications.store');
    Route::patch('opportunities/applications/{application}', [OpportunityController::class, 'updateApplication'])->middleware('role:super_admin,admin,staff')->name('opportunities.applications.update');
    Route::delete('opportunities/applications/{application}', [OpportunityController::class, 'destroyApplication'])->middleware('role:super_admin,admin,staff')->name('opportunities.applications.destroy');
    Route::resource('opportunities', OpportunityController::class)->except(['show'])->parameters(['opportunities' => 'opportunity']);
    Route::get('opportunities/{opportunity}', [OpportunityController::class, 'show'])->name('opportunities.show');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/data/{type}', [ReportController::class, 'data'])->name('reports.data');
    Route::get('reports/export/{type}/{format}', [ReportController::class, 'export'])->name('reports.export');

    // Finance (admin+)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
        Route::delete('payments/{payment}', [InvoiceController::class, 'destroyPayment'])->name('payments.destroy');
        Route::get('payments', [InvoiceController::class, 'payments'])->name('payments.index');
        Route::resource('fee-structures', FeeStructureController::class)->except(['show', 'create'])->parameters(['fee-structures' => 'feeStructure']);
    });

    // Academic (admin+): batches, intakes, semesters, student movements
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('batches', BatchController::class)->parameters(['batches' => 'batch']);
        Route::get('student-movements', [StudentMovementController::class, 'index'])->name('student-movements.index');
        Route::get('student-movements/create', [StudentMovementController::class, 'create'])->name('student-movements.create');
        Route::post('student-movements', [StudentMovementController::class, 'store'])->name('student-movements.store');
        Route::delete('student-movements/{movement}', [StudentMovementController::class, 'destroy'])->name('student-movements.destroy');
    });

    // HR (admin+): employees and employee card. Positions is an org-wide catalog.
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('employees/{employee}/card', [EmployeeController::class, 'card'])->name('employees.card');
        Route::resource('employees', EmployeeController::class)->parameters(['employees' => 'employee']);
    });

    // Organization-wide catalogs: only the all-campus account may change these.
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('programs', ProgramController::class)->except(['show'])->parameters(['programs' => 'program']);
        Route::resource('intakes', IntakeController::class)->except(['show', 'create'])->parameters(['intakes' => 'intake']);
        Route::resource('semesters', SemesterController::class)->except(['show', 'create'])->parameters(['semesters' => 'semester']);
        Route::resource('positions', PositionController::class)->except(['show', 'create'])->parameters(['positions' => 'position']);
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('campuses', CampusController::class)->except(['show', 'create']);
        Route::get('school-clubs', [SchoolClubController::class, 'index'])->name('school-clubs.index');
        Route::post('school-clubs', [SchoolClubController::class, 'store'])->name('school-clubs.store');
        Route::put('school-clubs/{schoolClub}', [SchoolClubController::class, 'update'])->name('school-clubs.update');
        Route::delete('school-clubs/{schoolClub}', [SchoolClubController::class, 'destroy'])->name('school-clubs.destroy');
        Route::resource('courses', CourseController::class)->except(['show', 'create']);
        Route::resource('course-categories', CourseCategoryController::class)->except(['show', 'create']);
        Route::resource('academic-years', AcademicYearController::class)->except(['show', 'create']);
        Route::get('backup', [BackupController::class, 'download'])->name('backup.download');
    });

    // Accounts: a campus-scoped admin is limited to their own campuses' accounts in
    // UserController, so the route group stays open to both admin roles.
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Documents overview
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/generate', [NotificationController::class, 'generate'])->name('notifications.generate');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Audit logs (super admin only)
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('role:super_admin')->name('audit-logs.index');
    Route::middleware(['auth', 'role:super_admin,admin,staff'])->group(function () {
        Route::post('/face/register', [
            StudentFaceController::class,
            'register'
        ])->name('face.register');

        Route::post('/face/identify', [
            FaceController::class,
            'identify'
        ])->name('face.identify');
    });

    Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
        Route::post('/face/register-employee', [
            EmployeeFaceController::class,
            'register'
        ])->name('face.register.employee');
    });
});

// Live fingerprint device ingest. Public (no session/auth) — secured by a shared
// device token header + throttle. See DeviceAttendanceController::punch().
Route::post('/attendance/device-punch', [DeviceAttendanceController::class, 'punch'])
    ->middleware('throttle:60,1')
    ->name('attendance.device-punch');
