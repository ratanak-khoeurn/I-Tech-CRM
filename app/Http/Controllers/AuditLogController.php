<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->with('user:id,name');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('description', 'like', "%$q%")->orWhere('action', 'like', "%$q%")->orWhere('entity_type', 'like', "%$q%"));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        $logs = $query->latest()->paginate(25)->withQueryString();
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action');
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('settings.audit-logs', compact('logs', 'actions', 'users'));
    }
}
