<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CampusController extends Controller
{
    /**
     * Display all campuses.
     */
    public function index()
    {
        $campuses = Campus::withCount([
            'studentCourses',
            'academicYears',
        ])
            ->orderBy('name')
            ->paginate(15);

        return view('settings.campuses', compact('campuses'));
    }

    /**
     * Store a new campus.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('campuses', 'code'),
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5000',
            ],
        ]);

        $data['is_active'] = $request->boolean(
            'is_active',
            true
        );

        // Upload campus image
        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('campuses', 'public');
        } else {
            unset($data['image']);
        }

        $campus = Campus::create($data);

        AuditLog::record(
            'created',
            'campus',
            $campus->id,
            "Created campus '{$campus->name}'"
        );

        return back()->with(
            'success',
            'Campus created successfully.'
        );
    }

    /**
     * Show campus edit page.
     */
    public function edit(Campus $campus)
    {
        return view(
            'settings.campus-edit',
            compact('campus')
        );
    }

    /**
     * Update campus.
     */
    public function update(
        Request $request,
        Campus $campus
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('campuses', 'code')
                    ->ignore($campus->id),
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5000',
            ],
        ]);

        $data['is_active'] = $request->boolean(
            'is_active',
            true
        );

        // Replace campus image
        if ($request->hasFile('image')) {

            if ($campus->image) {
                Storage::disk('public')->delete(
                    $campus->image
                );
            }

            $data['image'] = $request
                ->file('image')
                ->store('campuses', 'public');

        } else {
            // Keep existing image
            unset($data['image']);
        }

        $campus->update($data);

        AuditLog::record(
            'updated',
            'campus',
            $campus->id,
            "Updated campus '{$campus->name}'"
        );

        return redirect()
            ->route('campuses.index')
            ->with(
                'success',
                'Campus updated successfully.'
            );
    }

    /**
     * Switch the campus the signed-in staff member is working in.
     * This only changes their default filter, not what they may access.
     */
    public function switchCampus(Request $request)
    {
        $data = $request->validate([
            'campus_id' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $assigned = CampusContext::assignable($user);

        if ($assigned === []) {
            return back()->with('error', 'Your account is not assigned to any campus.');
        }

        $value = $data['campus_id'];

        if ($value === CampusContext::ALL) {
            $request->session()->put(CampusContext::SESSION_KEY, CampusContext::ALL);

            return back()->with('success', 'Showing all of your campuses.');
        }

        if (!ctype_digit($value) || !in_array((int) $value, $assigned, true)) {
            abort(403, 'You are not assigned to that campus.');
        }

        $request->session()->put(CampusContext::SESSION_KEY, (int) $value);

        return back()->with('success', 'Switched to '
            . Campus::withoutGlobalScopes()->whereKey($value)->value('name') . '.');
    }

    /**
     * Delete campus.
     */
    public function destroy(Campus $campus)
    {
        // Do not delete campus if it has student records.
        if ($campus->studentCourses()->exists()) {
            return back()->with(
                'error',
                'Cannot delete: this campus has student records. Deactivate it instead.'
            );
        }

        // Do not delete campus if it has academic years.
        if ($campus->academicYears()->exists()) {
            return back()->with(
                'error',
                'Cannot delete: this campus has academic years. Delete or move the academic years first.'
            );
        }

        // Delete campus image.
        if ($campus->image) {
            Storage::disk('public')->delete(
                $campus->image
            );
        }

        $campusName = $campus->name;
        $campusId = $campus->id;

        $campus->delete();

        AuditLog::record(
            'deleted',
            'campus',
            $campusId,
            "Deleted campus '{$campusName}'"
        );

        return back()->with(
            'success',
            'Campus deleted successfully.'
        );
    }
}
