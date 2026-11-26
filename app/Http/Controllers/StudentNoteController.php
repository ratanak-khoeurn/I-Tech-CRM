<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentNoteController extends Controller
{
    public function store(Request $request, Student $student)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($student), 403, 'You can only manage notes for students assigned to you.');
        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        $student->notes()->create([
            'content' => $data['content'],
            'is_private' => $request->boolean('is_private'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('students.show', [$student, 'tab' => 'notes'])->with('success', 'Note added.');
    }

    public function destroy(StudentNote $note)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($note->student), 403, 'You can only manage notes for students assigned to you.');
        if ($note->is_private && !Auth::user()->isAdmin()) {
            abort(403, 'Only admins can delete private notes.');
        }
        $studentId = $note->student_id;
        $note->delete();

        return redirect()->route('students.show', [$studentId, 'tab' => 'notes'])->with('success', 'Note deleted.');
    }
}
