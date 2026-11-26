<?php

namespace App\Models\Concerns;

use App\Scopes\CampusScope;

/**
 * Restricts a campus-column model to the campuses of the signed-in account.
 *
 * Override campusScopeColumn() when the column is not called campus_id, and
 * campusScopeIncludesNull() when a null campus means "shared by every campus".
 */
trait CampusScoped
{
    public static function bootCampusScoped(): void
    {
        static::addGlobalScope(new CampusScope(static::campusScopeColumn(), static::campusScopeIncludesNull()));
    }

    public static function campusScopeColumn(): string
    {
        return 'campus_id';
    }

    public static function campusScopeIncludesNull(): bool
    {
        return false;
    }
}
