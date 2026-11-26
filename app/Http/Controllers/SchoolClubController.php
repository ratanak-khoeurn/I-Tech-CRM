<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SchoolClub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SchoolClubController extends Controller
{
    /**
     * Display all school clubs.
     */
    public function index()
    {
        $clubs = SchoolClub::withCount('students')
            ->orderBy('name')
            ->get();

        return view('settings.school-clubs', compact('clubs'));
    }

    /**
     * Store a new school club.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_clubs', 'name'),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_clubs', 'code'),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],

            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'max:10'],
            'billing_period' => ['nullable', 'string', 'max:50'],

            'capacity' => ['nullable', 'integer', 'min:0'],

            'location' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:2000'],

            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'registration_start' => ['nullable', 'date'],
            'registration_end' => [
                'nullable',
                'date',
                'after_or_equal:registration_start',
            ],

            'coach_name' => ['nullable', 'string', 'max:255'],
            'coach_email' => ['nullable', 'email', 'max:255'],
            'coach_phone' => ['nullable', 'string', 'max:30'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $image = $request->file('image');
        unset($data['image']);

        if ($image) {
            $data['image'] = $image->store('school-clubs', 'public');
        }

        // Apply defaults when optional form fields are omitted.
        $data['price'] = $data['price'] ?? 0;
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($data) {
            $club = SchoolClub::create($data);

            AuditLog::record(
                'created',
                'school_club',
                $club->id,
                "Created school club '{$club->name}'"
            );
        });

        return redirect()
            ->route('school-clubs.index')
            ->with('success', 'School club created successfully.');
    }

    /**
     * Update an existing school club.
     */
    public function update(Request $request, SchoolClub $schoolClub)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_clubs', 'name')->ignore($schoolClub->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_clubs', 'code')->ignore($schoolClub->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'max:10'],
            'billing_period' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'registration_start' => ['nullable', 'date'],
            'registration_end' => ['nullable', 'date', 'after_or_equal:registration_start'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'coach_email' => ['nullable', 'email', 'max:255'],
            'coach_phone' => ['nullable', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $newImage = $request->file('image');
        $oldImage = $schoolClub->image;
        unset($data['image']);

        if ($newImage) {
            $data['image'] = $newImage->store('school-clubs', 'public');
        }

        $data['price'] = $data['price'] ?? 0;
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($schoolClub, $data) {
            $schoolClub->update($data);

            AuditLog::record(
                'updated',
                'school_club',
                $schoolClub->id,
                "Updated school club '{$schoolClub->name}'"
            );
        });

        if ($newImage && $oldImage && $oldImage !== $schoolClub->image) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('school-clubs.index')
            ->with('success', 'School club updated successfully.');
    }

    /**
     * Delete a school club.
     */
    public function destroy(SchoolClub $schoolClub)
    {
        $clubId = $schoolClub->id;
        $clubName = $schoolClub->name;
        $clubImage = $schoolClub->image;

        DB::transaction(function () use ($schoolClub, $clubId, $clubName) {
            $schoolClub->delete();

            AuditLog::record(
                'deleted',
                'school_club',
                $clubId,
                "Deleted school club '{$clubName}'"
            );
        });

        if ($clubImage) {
            Storage::disk('public')->delete($clubImage);
        }

        return redirect()
            ->route('school-clubs.index')
            ->with(
                'success',
                'School club deleted successfully. Student assignments are removed if the database foreign key is configured to cascade deletes.'
            );
    }
}
