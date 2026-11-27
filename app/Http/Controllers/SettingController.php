<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'settings' => Setting::all()->keyBy('key'),
            'roles' => Role::withCount('users')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'center_name' => ['required', 'string', 'max:255'],
            'center_email' => ['nullable', 'email', 'max:255'],
            'center_phone' => ['nullable', 'string', 'max:50'],
            'center_address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5000'],
            'notify_followup' => ['nullable', 'boolean'],
            'notify_overdue' => ['nullable', 'boolean'],
            'notify_events' => ['nullable', 'boolean'],
            'notify_opportunities' => ['nullable', 'boolean'],
            'backup_frequency' => ['nullable', 'string', 'max:50'],
            'backup_retention' => ['nullable', 'integer', 'min:1', 'max:365'],
            'riel_rate' => ['nullable', 'numeric', 'min:100', 'max:20000'],
        ]);

        Setting::set('center_name', $data['center_name'], 'general');
        Setting::set('center_email', $data['center_email'] ?? '', 'general');
        Setting::set('center_phone', $data['center_phone'] ?? '', 'general');
        Setting::set('center_address', $data['center_address'] ?? '', 'general');
        foreach (['notify_followup', 'notify_overdue', 'notify_events', 'notify_opportunities'] as $k) {
            Setting::set($k, $request->boolean($k) ? '1' : '0', 'notifications');
        }
        Setting::set('backup_frequency', $data['backup_frequency'] ?? 'weekly', 'backup');
        Setting::set('backup_retention', (string) ($data['backup_retention'] ?? 30), 'backup');
        Setting::set('riel_rate', (string) round((float) ($data['riel_rate'] ?? \App\Support\Money::DEFAULT_RATE)), 'finance');

        if ($request->hasFile('logo')) {
            Setting::set('center_logo', $request->file('logo')->store('settings', 'public'), 'general');
        }

        AuditLog::record('updated', 'settings', null, 'Updated system settings');

        return back()->with('success', 'Settings saved successfully.');
    }
}
