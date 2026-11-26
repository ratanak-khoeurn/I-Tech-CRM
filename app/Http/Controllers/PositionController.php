<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::query()->withCount('employees')->orderBy('department')->orderBy('title');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%$q%")->orWhere('department', 'like', "%$q%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }
        $positions = $query->paginate(15)->withQueryString();

        return view('hr.positions', compact('positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);
        $position = Position::create($data);
        AuditLog::record('created', 'position', $position->id, "Created position '{$position->title}'");

        return back()->with('success', 'Position created.');
    }

    public function edit(Position $position)
    {
        return view('hr.position-edit', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);
        $position->update($data);
        AuditLog::record('updated', 'position', $position->id, "Updated position '{$position->title}'");

        return redirect()->route('positions.index')->with('success', 'Position updated.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->exists()) {
            return back()->with('error', 'Cannot delete: employees hold this position. Deactivate it instead.');
        }
        $position->delete();
        AuditLog::record('deleted', 'position', $position->id, "Deleted position '{$position->title}'");

        return back()->with('success', 'Position deleted.');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:1', 'max:99'],
            'description' => ['nullable', 'string'],
        ];
    }
}
