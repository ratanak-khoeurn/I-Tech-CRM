<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Intake;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IntakeController extends Controller
{
    public function index(Request $request)
    {
        $query = Intake::query()->withCount('batches')->orderByDesc('academic_year')->orderBy('name');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }
        $intakes = $query->paginate(15)->withQueryString();

        return view('academic.intakes', compact('intakes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);
        $intake = Intake::create($data);
        AuditLog::record('created', 'intake', $intake->id, "Created intake '{$intake->name}'");

        return back()->with('success', 'Intake created.');
    }

    public function edit(Intake $intake)
    {
        return view('academic.intake-edit', compact('intake'));
    }

    public function update(Request $request, Intake $intake)
    {
        $data = $request->validate($this->rules($intake));
        $data['is_active'] = $request->boolean('is_active', true);
        $intake->update($data);
        AuditLog::record('updated', 'intake', $intake->id, "Updated intake '{$intake->name}'");

        return redirect()->route('intakes.index')->with('success', 'Intake updated.');
    }

    public function destroy(Intake $intake)
    {
        if ($intake->batches()->exists()) {
            return back()->with('error', 'Cannot delete: batches are linked to this intake. Deactivate it instead.');
        }
        $intake->delete();
        AuditLog::record('deleted', 'intake', $intake->id, "Deleted intake '{$intake->name}'");

        return back()->with('success', 'Intake deleted.');
    }

    private function rules(?Intake $intake = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('intakes', 'code')->ignore($intake?->id)],
            'academic_year' => ['nullable', 'integer', 'min:1900', 'max:2999'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'description' => ['nullable', 'string'],
        ];
    }
}
