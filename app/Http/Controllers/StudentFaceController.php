<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFaceProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class StudentFaceController extends Controller
{
    // Register a student's face.
    public function register(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'image' => 'required|image|max:5120',
            'consent_confirmed' => 'required|accepted',
        ]);

        $student = Student::findOrFail($request->integer('student_id'));
        $user = $request->user();

        abort_unless(
            $user->canManageStudents() && ($user->isAdmin() || (int) $student->assigned_staff_id === (int) $user->id),
            403
        );

        if ($student->record_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active students can be enrolled for face attendance.',
            ], 422);
        }

        $image = $request->file('image');

        try {
            $response = Http::timeout(30)
                ->attach(
                    'file',
                    file_get_contents($image->getRealPath()),
                    $image->getClientOriginalName()
                )
                ->post(config('services.face.url') . '/embedding');
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'The face recognition service is unavailable. Start the Python face service and try again.',
            ], 503);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => $response->json('detail', 'Face processing failed.')
            ], 422);
        }

        $profile = StudentFaceProfile::updateOrCreate(
            ['student_id' => $request->student_id],
            [
                'embedding' => $response->json('embedding'),
                'model_version' => $response->json('model_version'),
                'is_active' => true,
                'consent_obtained_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Face profile registered successfully.',
            'student_id' => $profile->student_id,
        ]);
    }
}
