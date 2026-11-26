<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::query()->withCount('courses')->orderBy('name')->paginate(15);

        return view('academic.programs', compact('programs'));
    }

    public function create()
    {
        return view('academic.program-edit', ['program' => new Program(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);
        $program = Program::create($data);
        AuditLog::record('created', 'program', $program->id, "Created program '{$program->name}'");

        return redirect()->route('programs.index')->with('success', 'Program created.');
    }

    public function edit(Program $program)
    {
        return view('academic.program-edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate($this->rules($program));
        $data['is_active'] = $request->boolean('is_active', true);
        $program->update($data);
        AuditLog::record('updated', 'program', $program->id, "Updated program '{$program->name}'");

        return redirect()->route('programs.index')->with('success', 'Program updated.');
    }

    public function destroy(Program $program)
    {
        if ($program->courses()->exists()) {
            return back()->with('error', 'Cannot delete a program with courses. Reassign or remove its courses first.');
        }

        $program->delete();
        AuditLog::record('deleted', 'program', $program->id, "Deleted program '{$program->name}'");

        return back()->with('success', 'Program deleted.');
    }

    private function rules(?Program $program = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('programs', 'name')->ignore($program?->id)],
            'code' => ['required', 'string', 'max:40', Rule::unique('programs', 'code')->ignore($program?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
