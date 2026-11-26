<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentDocument::query()->with(['student:id,name_english,student_code', 'uploader:id,name']);

        if (!Auth::user()->canViewSensitiveData()) {
            $query->where('is_sensitive', false);
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%$q%")
                ->orWhereHas('student', fn ($w2) => $w2->where('name_english', 'like', "%$q%")->orWhere('student_code', 'like', "%$q%")));
        }
        if ($request->filled('doc_type')) {
            $query->where('doc_type', $request->input('doc_type'));
        }

        $documents = $query->latest()->paginate(15)->withQueryString();
        $students = Student::where('record_status', 'active')->orderBy('name_english')->get(['id', 'name_english', 'student_code']);

        return view('documents.index', compact('documents', 'students'));
    }

    public function store(Request $request, Student $student)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($student), 403, 'You can only manage documents for students assigned to you.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'doc_type' => ['required', Rule::in(array_keys(StudentDocument::TYPES))],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_sensitive' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('file')->store('students/documents', 'public');
        $doc = StudentDocument::create([
            'student_id' => $student->id,
            'name' => $data['name'],
            'doc_type' => $data['doc_type'],
            'file_path' => $path,
            'file_size' => $request->file('file')->getSize(),
            'description' => $data['description'] ?? null,
            'is_sensitive' => $request->boolean('is_sensitive'),
            'uploaded_by' => Auth::id(),
        ]);

        AuditLog::record('uploaded', 'student_document', $doc->id, "Uploaded document '{$doc->name}' for {$student->name_english}");

        return redirect()->route('students.show', [$student, 'tab' => 'documents'])->with('success', 'Document uploaded successfully.');
    }

    public function download(StudentDocument $document)
    {
        if ($document->is_sensitive && !Auth::user()->canViewSensitiveData()) {
            abort(403, 'This document is restricted to administrators.');
        }
        if (!Auth::user()->isAdmin() && Auth::user()->canManageStudents() && !Auth::user()->canAccessStudent($document->student)) {
            abort(403, 'You can only access documents for students assigned to you.');
        }
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on disk.');
        }
        AuditLog::record('downloaded', 'student_document', $document->id, "Downloaded document '{$document->name}'");

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    public function destroy(StudentDocument $document)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        abort_unless(Auth::user()->canAccessStudent($document->student), 403, 'You can only manage documents for students assigned to you.');
        $studentId = $document->student_id;
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        AuditLog::record('deleted', 'student_document', $document->id, 'Deleted document');

        return redirect()->route('students.show', [$studentId, 'tab' => 'documents'])->with('success', 'Document deleted.');
    }
}
