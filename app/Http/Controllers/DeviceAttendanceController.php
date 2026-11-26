<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Services\AttendanceRecorder;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use SplFileObject;

class DeviceAttendanceController extends Controller
{
    /**
     * Streams a sample punch-log CSV so admins know the exact columns the
     * importer expects (export this from the fingerprint terminal's software).
     */
    public function template()
    {
        $csv = "user_id,person_type,date,time\n"
            . "STU20260001,student,2026-11-06,08:15:00\n"
            . "STU20260001,student,2026-11-06,12:00:00\n"
            . "EMP-2026-0001,employee,2026-11-06,07:50:00\n"
            . "EMP-2026-0001,employee,2026-11-06,17:30:00\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance-punch-template.csv"',
        ]);
    }

    public function import(Request $request, AttendanceRecorder $recorder)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:8192'],
        ]);

        $rows = $this->readCsv($request->file('file')->getRealPath());

        if (!$rows) {
            return back()->withErrors(['file' => 'No data rows were found. Use the CSV exported from the device (see the sample template).']);
        }

        [$header, $dataRows] = $rows;
        $columns = $this->mapColumns($header);

        if ($columns['identifier'] === null) {
            return back()->withErrors(['file' => 'Could not find an ID column. Add a user_id / code / employee_code column, or download the sample template.']);
        }

        if ($columns['datetime'] === null && ($columns['date'] === null || $columns['time'] === null)) {
            return back()->withErrors(['file' => 'Could not find a timestamp. Provide a single date/time column, or separate date and time columns.']);
        }

        $summary = [
            'checked_in' => 0,
            'checked_out' => 0,
            'complete' => 0,
            'not_found' => 0,
            'ambiguous' => 0,
            'invalid' => 0,
            'issues' => [],
        ];

        $personCache = [];

        foreach ($dataRows as $index => $row) {
            $lineNumber = $index + 2; // +1 for header, +1 for 1-based display

            $identifier = trim((string) ($row[$columns['identifier']] ?? ''));

            if ($identifier === '') {
                continue;
            }

            $at = $this->parseTimestamp($row, $columns);

            if ($at === null) {
                $summary['invalid']++;
                $summary['issues'][] = "Row {$lineNumber}: could not read a valid date/time.";
                continue;
            }

            $type = strtolower(trim((string) ($columns['type'] !== null ? ($row[$columns['type']] ?? '') : '')));

            if (!in_array($type, ['student', 'employee'], true)) {
                $type = 'auto';
            }

            $person = $this->resolvePerson($identifier, $type, $personCache);

            if ($person === 'ambiguous') {
                $summary['ambiguous']++;
                $summary['issues'][] = "Row {$lineNumber}: ID '{$identifier}' matches both a student and an employee. Set the person_type column.";
                continue;
            }

            if ($person === null) {
                $summary['not_found']++;
                $summary['issues'][] = "Row {$lineNumber}: no active person found for ID '{$identifier}'.";
                continue;
            }

            $result = $recorder->record($person, $at, Auth::id());
            $summary[$result['action']]++;
        }

        $summary['total'] = array_sum([
            $summary['checked_in'],
            $summary['checked_out'],
            $summary['complete'],
            $summary['not_found'],
            $summary['ambiguous'],
            $summary['invalid'],
        ]);

        return redirect()
            ->route('attendance.finger', ['date' => now()->toDateString()])
            ->with('device_import', $summary);
    }

    /**
     * Live punch ingest for fingerprint terminals / LAN bridges.
     *
     * Authenticated by a shared device token (header), NOT by the web session,
     * so a standalone device can POST directly. Mirrors the CSV importer's
     * person-resolution and the on-screen scanner's check-in/out toggle.
     *
     * Expected JSON body:
     *   { "code": "<student_code|employee_code>",  // or "user_id"
     *     "person_type": "student|employee|auto",  // optional
     *     "datetime": "2026-11-06 08:15:00",        // or "date" + "time"
     *     "campus_id": 3, "device_id": "GATE-01" }  // optional
     * Header: X-Device-Token: <ATTENDANCE_DEVICE_TOKEN>  (or Authorization: Bearer <token>)
     */
    public function punch(Request $request, AttendanceRecorder $recorder)
    {
        $configured = trim((string) config('services.attendance_device.token', ''));

        if ($configured === '') {
            return response()->json([
                'success' => false,
                'error' => 'device_not_configured',
                'message' => 'Live device ingest is disabled: set ATTENDANCE_DEVICE_TOKEN in .env.',
            ], 503);
        }

        $bearer = str_ireplace('Bearer ', '', trim((string) $request->header('Authorization', '')));
        $provided = trim((string) ($request->header('X-Device-Token') ?: $bearer));

        if ($provided === '' || !hash_equals($configured, $provided)) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'Missing or invalid device token.',
            ], 401);
        }

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:64'],
            'user_id' => ['nullable', 'string', 'max:64'],
            'person_type' => ['nullable', 'string', 'in:student,employee,auto'],
            'datetime' => ['nullable', 'string', 'max:64'],
            'date' => ['nullable', 'string', 'max:64'],
            'time' => ['nullable', 'string', 'max:64'],
            'campus_id' => CampusContext::campusRules(false),
            'device_id' => ['nullable', 'string', 'max:64'],
        ]);

        $identifier = trim((string) ($data['code'] ?? '') !== '' ? $data['code'] : ($data['user_id'] ?? ''));

        if ($identifier === '') {
            return response()->json([
                'success' => false,
                'error' => 'invalid_payload',
                'message' => 'A user_id / code is required.',
            ], 422);
        }

        $at = $this->punchTimestamp($data);

        if ($at === null) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_payload',
                'message' => 'Provide a datetime, or separate date and time values.',
            ], 422);
        }

        $type = strtolower(trim((string) ($data['person_type'] ?? 'auto')));
        if (!in_array($type, ['student', 'employee', 'auto'], true)) {
            $type = 'auto';
        }

        $cache = [];
        $person = $this->resolvePerson($identifier, $type, $cache);

        if ($person === 'ambiguous') {
            return response()->json([
                'success' => false,
                'error' => 'ambiguous',
                'message' => "ID '{$identifier}' matches both a student and an employee. Send person_type.",
                'user_id' => $identifier,
            ], 409);
        }

        if ($person === null) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => "No active person found for ID '{$identifier}'.",
                'user_id' => $identifier,
            ], 404);
        }

        $campusId = isset($data['campus_id']) ? (int) $data['campus_id'] : null;
        $result = $recorder->record($person, $at, null, $campusId);

        return response()->json([
            'success' => true,
            'action' => $result['action'],
            'message' => $result['message'],
            'remark' => $result['remark'] ?? null,
            'person' => [
                'type' => $person instanceof Student ? 'student' : 'employee',
                'code' => $person instanceof Student ? $person->student_code : $person->employee_code,
                'name' => $result['name'],
            ],
            'at' => $at->toDateTimeString(),
        ], 201);
    }

    private function punchTimestamp(array $data): ?Carbon
    {
        try {
            $dateTime = trim((string) ($data['datetime'] ?? ''));
            if ($dateTime !== '') {
                return Carbon::parse($dateTime);
            }

            $date = trim((string) ($data['date'] ?? ''));
            $time = trim((string) ($data['time'] ?? ''));

            if ($date === '') {
                return null;
            }

            return Carbon::parse($date . ($time !== '' ? ' ' . $time : ''));
        } catch (\Throwable) {
            return null;
        }
    }

    private function readCsv(string $path): ?array
    {
        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $header = null;
        $dataRows = [];

        foreach ($file as $line) {
            if ($line === [null] || $line === false) {
                continue;
            }

            if ($header === null) {
                $header = $line;
                continue;
            }

            // Drop fully blank rows.
            if (count(array_filter($line, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                continue;
            }

            $dataRows[] = $line;
        }

        if ($header === null) {
            return null;
        }

        return [$header, $dataRows];
    }

    private function mapColumns(array $header): array
    {
        $normalized = [];

        foreach ($header as $position => $name) {
            $key = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower((string) $name)));
            if ($key !== '') {
                $normalized[$key] = $position;
            }
        }

        $pick = function (array $candidates) use ($normalized): ?int {
            foreach ($candidates as $candidate) {
                if (isset($normalized[$candidate])) {
                    return $normalized[$candidate];
                }
            }
            return null;
        };

        return [
            'identifier' => $pick(['usrid', 'userid', 'id', 'pin', 'cardno', 'card', 'employeecode', 'studentcode', 'code', 'number']),
            'type' => $pick(['persontype', 'type']),
            'date' => $pick(['date', 'attdate', 'checkdate', 'day']),
            'time' => $pick(['time', 'checktime', 'atttime']),
            'datetime' => $pick(['datetime', 'timestamp', 'punch', 'punchtime', 'checkdatetime', 'checkindate', 'dateime']),
        ];
    }

    private function parseTimestamp(array $row, array $columns): ?Carbon
    {
        try {
            if ($columns['datetime'] !== null) {
                $raw = trim((string) ($row[$columns['datetime']] ?? ''));
                return $raw === '' ? null : Carbon::parse($raw);
            }

            $date = trim((string) ($row[$columns['date']] ?? ''));
            $time = trim((string) ($row[$columns['time']] ?? ''));

            if ($date === '') {
                return null;
            }

            return Carbon::parse($date . ($time !== '' ? ' ' . $time : ''));
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolvePerson(string $identifier, string $type, array &$cache): Student|Employee|null|string
    {
        $cacheKey = $type . '|' . $identifier;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $student = in_array($type, ['student', 'auto'], true)
            ? Student::where('student_code', $identifier)->where('record_status', 'active')->first()
            : null;

        $employee = in_array($type, ['employee', 'auto'], true)
            ? Employee::where('employee_code', $identifier)->where('status', 'active')->first()
            : null;

        $result = match (true) {
            $type === 'student' => $student,
            $type === 'employee' => $employee,
            $student && $employee => 'ambiguous',
            $student => $student,
            $employee => $employee,
            default => null,
        };

        return $cache[$cacheKey] = $result;
    }
}
