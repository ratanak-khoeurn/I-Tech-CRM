<?php

namespace App\Scopes;

use App\Support\CampusContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides rows whose campus is outside the signed-in account's boundary.
 *
 * A no-op for super_admin, for portal roles and outside HTTP requests (console,
 * queue), so scheduled jobs and seeders still see every row.
 */
class CampusScope implements Scope
{
    public function __construct(private string $column = 'campus_id', private bool $includeNull = false)
    {
    }

    public function apply(Builder $query, Model $model): void
    {
        $visible = CampusContext::visibleIds();
        if ($visible === null) {
            return;
        }
        if (!$this->includeNull) {
            $query->whereIn($this->column, $visible);

            return;
        }
        // A null campus means the row is shared by every campus, so it stays usable.
        $query->where(fn (Builder $w) => $w->whereNull($this->column)->orWhereIn($this->column, $visible));
    }
}
