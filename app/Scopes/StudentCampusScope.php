<?php

namespace App\Scopes;

use App\Support\CampusContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides rows belonging to a student the signed-in account may not work with.
 *
 * The boundary lives on students.campus_id, so the student subquery inherits it
 * instead of every related table needing its own campus column.
 */
class StudentCampusScope implements Scope
{
    public function __construct(private string $column = 'student_id')
    {
    }

    public function apply(Builder $query, Model $model): void
    {
        CampusContext::whereVisibleStudent($query, $this->column);
    }
}
