<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeStructureController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeStructure::query()->with(['course', 'campus'])->orderBy('name');
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }
        if ($request->filled('fee_type')) {
            $query->where('fee_type', $request->input('fee_type'));
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where('name', 'like', "%$q%");
        }
        $fees = $query->paginate(15)->withQueryString();
        $courses = Course::orderBy('name')->get(['id', 'name']);
        $campuses = Campus::orderBy('name')->get(['id', 'name']);

        return view('finance.fees', compact('fees', 'courses', 'campuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);
        FeeStructure::create($data);
        AuditLog::record('created', 'fee_structure', null, "Created fee '{$data['name']}'");

        return back()->with('success', 'Fee structure created.');
    }

    public function edit(FeeStructure $feeStructure)
    {
        $courses = Course::orderBy('name')->get(['id', 'name']);
        $campuses = Campus::orderBy('name')->get(['id', 'name']);

        return view('finance.fee-edit', compact('feeStructure', 'courses', 'campuses'));
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $data = $request->validate($this->rules($feeStructure));
        $data['is_active'] = $request->boolean('is_active', true);
        $feeStructure->update($data);
        AuditLog::record('updated', 'fee_structure', $feeStructure->id, "Updated fee '{$feeStructure->name}'");

        return redirect()->route('fee-structures.index')->with('success', 'Fee structure updated.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        if ($feeStructure->invoices()->exists()) {
            return back()->with('error', 'Cannot delete: invoices already use this fee. Deactivate it instead.');
        }
        $feeStructure->delete();
        AuditLog::record('deleted', 'fee_structure', $feeStructure->id, "Deleted fee '{$feeStructure->name}'");

        return back()->with('success', 'Fee structure deleted.');
    }

    private function rules(?FeeStructure $fee = null): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'campus_id' => CampusContext::campusRules(false),
            'name' => ['required', 'string', 'max:255'],
            'fee_type' => ['required', Rule::in(array_keys(FeeStructure::FEE_TYPES))],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'frequency' => ['required', Rule::in(array_keys(FeeStructure::FREQUENCIES))],
        ];
    }
}
