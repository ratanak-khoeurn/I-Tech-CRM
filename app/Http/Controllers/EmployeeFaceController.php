<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFaceProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class EmployeeFaceController extends Controller
{
    // Register an employee's face for attendance scanning.
    public function register(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'image' => 'required|image|max:5120',
            'consent_confirmed' => 'required|accepted',
        ]);

        $employee = Employee::findOrFail($request->integer('employee_id'));
        $user = $request->user();

        abort_unless($user->isAdmin(), 403);

        if ($employee->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active employees can be enrolled for face attendance.',
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

        $profile = EmployeeFaceProfile::updateOrCreate(
            ['employee_id' => $employee->id],
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
            'employee_id' => $profile->employee_id,
        ]);
    }
}
