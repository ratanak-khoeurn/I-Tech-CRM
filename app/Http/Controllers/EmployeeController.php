<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display employees with filters and pagination.
     */
    public function index(Request $request)
    {
        $query = Employee::query()
            ->with(['position', 'campus'])
            ->orderBy('first_name');

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->input('position_id'));
        }

        if (($campusId = CampusContext::filterFor($request)) !== null) {
            $query->where('campus_id', $campusId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('employee_code', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $employees = $query->paginate(15)->withQueryString();

        return view('hr.employees', [
            'employees' => $employees,
            'positions' => Position::orderBy('title')->get(['id', 'title']),
            'campuses' => Campus::orderBy('name')->get(['id', 'name']),
            'statuses' => Employee::STATUSES,
        ]);
    }

    /**
     * Show the employee creation form.
     */
    public function create()
    {
        $employee = new Employee([
            'status' => 'active',
            'employment_type' => 'full_time',
        ]);

        return view(
            'hr.employee-form',
            $this->formData($employee)
        );
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['employee_code'] = $data['employee_code']
            ?: Employee::generateEmployeeCode();

        $data['created_by'] = Auth::id();

        if (!Auth::user()->canViewSensitiveData()) {
            unset($data['salary']);
        }

        $employee = new Employee($data);
        $newPhoto = null;

        // Upload the photo before saving the employee.
        if ($request->hasFile('photo')) {
            $newPhoto = $request->file('photo')
                ->store('employees', 'public');

            if (!$newPhoto) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to upload the employee photo.');
            }

            $employee->photo = $newPhoto;
        }

        try {
            $employee->save();
        } catch (\Throwable $exception) {
            // Remove the uploaded photo if saving fails.
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        AuditLog::record(
            'created',
            'employee',
            $employee->id,
            "Created employee '{$employee->fullName()}'"
        );

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee created.');
    }

    /**
     * Display an employee's details.
     */
    public function show(Employee $employee)
    {
        $employee->load(['position', 'campus', 'user']);

        return view('hr.employee-show', [
            'employee' => $employee,
            'canViewSensitive' => Auth::user()->canViewSensitiveData(),
        ]);
    }

    /**
     * Display an employee card.
     */
    public function card(Employee $employee)
    {
        $employee->load(['position', 'campus']);

        return view('hr.employee-card', [
            'employee' => $employee,
            'centerName' => \App\Models\Setting::get(
                'center_name',
                'Education Center'
            ),
        ]);
    }

    /**
     * Show the employee edit form.
     */
    public function edit(Employee $employee)
    {
        return view(
            'hr.employee-form',
            $this->formData($employee)
        );
    }

    /**
     * Update an existing employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate($this->rules($employee));

        if (!Auth::user()->canViewSensitiveData()) {
            unset($data['salary']);
        }

        $oldPhoto = $employee->photo;
        $newPhoto = null;

        // Upload a replacement photo if one was selected.
        if ($request->hasFile('photo')) {
            $newPhoto = $request->file('photo')
                ->store('employees', 'public');

            if (!$newPhoto) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to upload the new employee photo.');
            }

            $data['photo'] = $newPhoto;
        } else {
            // Preserve the existing photo when no new file is uploaded.
            unset($data['photo']);
        }

        $employee->fill($data);

        try {
            $employee->save();
        } catch (\Throwable $exception) {
            // Preserve the old photo if the database update fails.
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        // Delete the old photo only after the update succeeds.
        if (
            $newPhoto
            && $oldPhoto
            && $oldPhoto !== $newPhoto
        ) {
            Storage::disk('public')->delete($oldPhoto);
        }

        AuditLog::record(
            'updated',
            'employee',
            $employee->id,
            "Updated employee '{$employee->fullName()}'"
        );

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee updated.');
    }

    /**
     * Delete an employee and their photo.
     */
    public function destroy(Employee $employee)
    {
        $oldPhoto = $employee->photo;

        $employee->delete();

        // Delete the photo after the employee record is deleted.
        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        AuditLog::record(
            'deleted',
            'employee',
            $employee->id,
            "Deleted employee #{$employee->id}"
        );

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee deleted.');
    }

    /**
     * Prepare form dropdown data.
     */
    private function formData(Employee $employee): array
    {
        return [
            'employee' => $employee,
            'positions' => Position::where('is_active', true)
                ->orderBy('title')
                ->get(),
            'campuses' => Campus::orderBy('name')
                ->get(['id', 'name']),
            'users' => User::orderBy('name')
                ->get(['id', 'name', 'email']),
            'statuses' => Employee::STATUSES,
            'types' => Employee::EMPLOYMENT_TYPES,
            'canViewSensitive' => Auth::user()->canViewSensitiveData(),
        ];
    }

    /**
     * Employee validation rules.
     */
    private function rules(?Employee $employee = null): array
    {
        return [
            'employee_code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('employees', 'employee_code')
                    ->ignore($employee?->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'campus_id' => CampusContext::campusRules(false),
            'gender' => [
                'nullable',
                Rule::in(['male', 'female', 'other']),
            ],
            'date_of_birth' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'employment_type' => [
                'required',
                Rule::in(array_keys(Employee::EMPLOYMENT_TYPES)),
            ],
            'status' => [
                'required',
                Rule::in(array_keys(Employee::STATUSES)),
            ],
            'salary' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999',
            ],
            'contract_end_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5000',
            ],
        ];
    }
}
