<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRecorder
{
    /**
     * Record a single attendance punch for a student or employee, applying the
     * same toggle used by the on-screen scanner: first punch of the day is a
     * check-in, the next is a check-out, and later punches are ignored.
     *
     * @return array{action:string, message:string, name:string, remark?:string}
     */
    public function record(Student|Employee $person, Carbon $at, ?int $recorderId, ?int $campusId = null): array
    {
        $isStudent = $person instanceof Student;
        $identityColumn = $isStudent ? 'student_id' : 'employee_id';

        $name = $isStudent
            ? ($person->name_english ?: $person->name_khmer)
            : $person->fullName();

        $campusId = $campusId ?? $this->deriveCampus($person);

        return DB::transaction(function () use ($identityColumn, $person, $campusId, $at, $recorderId, $name) {
            $record = AttendanceRecord::query()
                ->where($identityColumn, $person->id)
                ->whereDate('attendance_date', $at->toDateString())
                ->lockForUpdate()
                ->first();

            if (!$record) {
                $remark = $this->lateRemark($at);

                $record = AttendanceRecord::create([
                    $identityColumn => $person->id,
                    'campus_id' => $campusId,
                    'attendance_date' => $at->toDateString(),
                    'check_in_at' => $at,
                    'checked_in_by' => $recorderId,
                    'remark' => $remark,
                ]);

                AuditLog::record('checked_in', 'attendance', $record->id, "{$name} checked in");

                return [
                    'action' => 'checked_in',
                    'message' => "Checked in: {$name} at " . $at->format('g:i A') . ($remark ? ' — ' . $remark : ''),
                    'name' => $name,
                    'remark' => $remark,
                ];
            }

            if (!$record->check_out_at) {
                $record->update([
                    'check_out_at' => $at,
                    'checked_out_by' => $recorderId,
                    'campus_id' => $record->campus_id ?: $campusId,
                ]);

                AuditLog::record('checked_out', 'attendance', $record->id, "{$name} checked out");

                return [
                    'action' => 'checked_out',
                    'message' => "Checked out: {$name} at " . $at->format('g:i A'),
                    'name' => $name,
                ];
            }

            return [
                'action' => 'complete',
                'message' => "Attendance is already complete for {$name} today.",
                'name' => $name,
            ];
        });
    }

    private function deriveCampus(Student|Employee $person): ?int
    {
        if ($person instanceof Employee) {
            return $person->campus_id;
        }

        return $person->campus_id
            ?? $person->activeEnrollment()?->campus_id
            ?? $person->latestStudentCourse()?->campus_id;
    }

    /**
     * A check-in later than the configured cutoff (services.attendance_late.after,
     * default 08:10) is flagged "Late". Times compare in the app timezone.
     */
    private function lateRemark(Carbon $at): ?string
    {
        return $at->greaterThan($this->checkInCutoff($at)) ? 'Late' : null;
    }

    private function checkInCutoff(Carbon $at): Carbon
    {
        $cutoff = $at->copy()->startOfDay();
        $configured = trim((string) config('services.attendance_late.after', '08:10'));

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $configured, $m)) {
            $cutoff = $cutoff->setTime((int) $m[1], (int) $m[2]);
        }

        return $cutoff;
    }
}
