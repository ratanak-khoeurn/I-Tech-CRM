<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFaceProfile;
use App\Models\Student;
use App\Models\StudentFaceProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FaceController extends Controller
{
    // Starting threshold for cosine similarity. Keep in sync with the Python
    // service; calibrate against real enrolled faces before relying on it.
    private const MATCH_THRESHOLD = 0.40;

    // Identify either an enrolled student or employee from a scanned face.
    public function identify(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $candidates = $this->candidateProfiles();

        if (!$candidates) {
            return response()->json([
                'success' => false,
                'message' => 'No face profiles are enrolled yet. Open a student or employee profile and enroll their face with consent first.',
            ], 409);
        }

        $image = $request->file('image');

        try {
            $embeddingResponse = Http::timeout(30)
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

        if (!$embeddingResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => $embeddingResponse->json('detail', 'Face processing failed.')
            ], 422);
        }

        $query = array_map('floatval', $embeddingResponse->json('embedding', []));

        $best = null;
        $bestScore = -1.0;

        foreach ($candidates as $candidate) {
            $score = $this->cosine($query, array_map('floatval', $candidate['embedding']));

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        if ($best === null || $bestScore < self::MATCH_THRESHOLD) {
            return response()->json([
                'success' => false,
                'message' => 'No matching person found. Try again with one face clearly visible.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'person_type' => $best['type'],
            'id' => $best['id'],
            'code' => $best['code'],
            'name' => $best['name'],
            'similarity' => round($bestScore, 4),
        ]);
    }

    private function candidateProfiles(): array
    {
        $candidates = [];

        StudentFaceProfile::query()
            ->where('is_active', true)
            ->whereHas('student', fn ($query) => $query->where('record_status', 'active'))
            ->with('student')
            ->each(function (StudentFaceProfile $profile) use (&$candidates) {
                $student = $profile->student;

                $candidates[] = [
                    'type' => 'student',
                    'id' => $student->id,
                    'code' => $student->student_code,
                    'name' => $student->name_english ?: $student->name_khmer,
                    'embedding' => $profile->embedding,
                ];
            });

        EmployeeFaceProfile::query()
            ->where('is_active', true)
            ->whereHas('employee', fn ($query) => $query->where('status', 'active'))
            ->with('employee')
            ->each(function (EmployeeFaceProfile $profile) use (&$candidates) {
                $employee = $profile->employee;

                $candidates[] = [
                    'type' => 'employee',
                    'id' => $employee->id,
                    'code' => $employee->employee_code,
                    'name' => $employee->fullName(),
                    'embedding' => $profile->embedding,
                ];
            });

        return $candidates;
    }

    private function cosine(array $a, array $b): float
    {
        if (count($a) !== count($b) || $a === []) {
            return -1.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * $b[$i];
            $normA += $value * $value;
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        return $denominator > 0 ? $dot / ($denominator + 1e-10) : -1.0;
    }
}
