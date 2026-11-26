<?php

namespace App\Models\Concerns;

use App\Scopes\StudentCampusScope;

/**
 * Restricts a student-owned record to the students of the signed-in account's campuses.
 *
 * Override campusScopeStudentColumn() when the column is not called student_id.
 */
trait StudentCampusScoped
{
    public static function bootStudentCampusScoped(): void
    {
        static::addGlobalScope(new StudentCampusScope(static::campusScopeStudentColumn()));
    }

    public static function campusScopeStudentColumn(): string
    {
        return 'student_id';
    }
}
