<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Resolves the campus boundary of the signed-in account.
 *
 * - super_admin is unrestricted and sees every campus.
 * - admin and staff are hard-bound to the campuses in the campus_user pivot: they
 *   cannot read, filter to, or write a campus they are not assigned to.
 * - portal roles (student) are not campus-bound at all; their access is decided by
 *   the owning-record checks in the controllers.
 *
 * The session "campus_id" value is only ever a *drill-down* inside that boundary —
 * the boundary itself is enforced by the model scopes, not by this key.
 */
class CampusContext
{
    public const SESSION_KEY = 'campus_id';

    public const ALL = 'all';

    /**
     * The campuses this account may work in, or null when it may work in all of them.
     *
     * Reads the pivot with the query builder on purpose: User::campusIds() goes
     * through the campuses relation, and the Campus model carries a global scope that
     * calls back into this method, so the relation would recurse into itself.
     *
     * @return list<int>|null
     */
    public static function visibleIds(?User $user = null): ?array
    {
        $user = $user ?: (Auth::check() ? Auth::user() : null);

        if (!$user || !$user->hasRole('super_admin', 'admin', 'staff')) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return null;
        }

        return array_map(
            fn ($id) => (int) $id,
            DB::table('campus_user')->where('user_id', $user->id)->pluck('campus_id')->all()
        );
    }

    /** Whether this account is limited to a set of campuses. */
    public static function isBounded(?User $user = null): bool
    {
        return self::visibleIds($user) !== null;
    }

    /** Whether this account may touch data of one campus (null = unassigned data). */
    public static function allows(?int $campusId, ?User $user = null): bool
    {
        $visible = self::visibleIds($user);

        return $visible === null || ($campusId !== null && in_array($campusId, $visible, true));
    }

    /** 403 when the account may not work in that campus. */
    public static function authorize(?int $campusId, ?User $user = null): void
    {
        if (!self::allows($campusId, $user)) {
            abort(403, 'That campus is outside your account scope.');
        }
    }

    /**
     * Apply the boundary to a query that carries its own campus_id column.
     *
     * @template T of Builder
     * @param  T  $query
     * @return T
     */
    public static function whereVisible(Builder $query, string $column = 'campus_id'): Builder
    {
        $visible = self::visibleIds();

        if ($visible !== null) {
            $query->whereIn($column, $visible);
        }

        return $query;
    }

    /**
     * Apply the boundary through the student a record belongs to: the Student query
     * already carries its own campus scope, so an out-of-scope student never matches.
     *
     * @template T of Builder
     * @param  T  $query
     * @return T
     */
    public static function whereVisibleStudent(Builder $query, string $column = 'student_id'): Builder
    {
        if (self::visibleIds() === null) {
            return $query;
        }

        $query->whereIn($column, Student::query()->select('id'));

        return $query;
    }

    /** Campus ids this user may pick from in a switcher, login form or filter. */
    public static function assignable(User $user): array
    {
        if (!self::managesData($user)) {
            return [];
        }

        $visible = self::visibleIds($user);

        if ($visible === null) {
            return array_map(fn ($id) => (int) $id, DB::table('campuses')->orderBy('name')->pluck('id')->all());
        }

        return $visible;
    }

    /**
     * The campus to prefilter by, or null when the page should show everything the
     * account is allowed to see.
     */
    public static function current(User $user): ?int
    {
        if (!self::managesData($user)) {
            return null;
        }

        $selected = session(self::SESSION_KEY);

        if ($selected === self::ALL) {
            return null;
        }

        $ids = self::assignable($user);

        if ($selected !== null && is_numeric($selected) && in_array((int) $selected, $ids, true)) {
            return (int) $selected;
        }

        // A campus-bound account always works inside one campus; the all-campus
        // account only drills down when it explicitly picks one.
        return self::visibleIds($user) === null ? null : ($ids[0] ?? null);
    }

    /** Whether the top-bar switcher is worth showing for this user. */
    public static function showsSwitcher(User $user): bool
    {
        if (!self::managesData($user)) {
            return false;
        }

        $assignable = self::assignable($user);

        if ($assignable === []) {
            return false;
        }

        return count($assignable) > 1 || self::isBounded($user);
    }

    /** Whether this account works inside the campus workspace at all. */
    public static function managesData(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'staff');
    }

    /**
     * Roles this account is allowed to hand out; null means every role exists for it.
     *
     * @return list<int>|null
     */
    public static function manageableRoleIds(?User $user = null): ?array
    {
        if (self::visibleIds($user) === null) {
            return null;
        }

        return array_map(
            fn ($id) => (int) $id,
            DB::table('roles')->whereIn('name', ['admin', 'staff'])->pluck('id')->all()
        );
    }

    /**
     * Restrict a users query to the accounts a campus-scoped admin may manage:
     * staff and campus admins who share at least one campus with them.
     *
     * @template T of Builder
     * @param  T  $query
     * @return T
     */
    public static function constrainUsers(Builder $query): Builder
    {
        $visible = self::visibleIds();

        if ($visible === null) {
            return $query;
        }

        $query->whereIn('role_id', self::manageableRoleIds())
            ->whereIn('id', function ($sub) use ($visible) {
                $sub->select('user_id')->from('campus_user')->whereIn('campus_id', $visible);
            });

        return $query;
    }

    /** Whether the signed-in account may edit or delete this account. */
    public static function allowsManagingUser(User $target): bool
    {
        $visible = self::visibleIds();

        if ($visible === null) {
            return true;
        }

        $roles = self::manageableRoleIds();

        if ($target->role_id === null || !in_array((int) $target->role_id, $roles, true)) {
            return false;
        }

        return DB::table('campus_user')
            ->where('user_id', $target->id)
            ->whereIn('campus_id', $visible)
            ->exists();
    }

    /**
     * The campus a list page should filter by: an explicit selection in the request
     * wins, but only when it is inside the account's boundary; otherwise the user's
     * active campus is the default.
     */
    public static function filterFor(Request $request): ?int
    {
        if ($request->has(self::SESSION_KEY)) {
            $value = $request->input(self::SESSION_KEY);

            if ($value === self::ALL) {
                return null;
            }

            if (is_numeric($value) && self::allows((int) $value)) {
                return (int) $value;
            }

            // Anything else (blank, unknown or outside the boundary) falls back to the
            // account's own default so a list never shows an empty other-campus filter.
        }

        $user = $request->user() ?? Auth::user();

        return $user ? self::current($user) : null;
    }

    /**
     * Validation rules for a campus_id the account is allowed to write.
     *
     * @return list<string|\Illuminate\Contracts\Validation\Rule|\Illuminate\Validation\Rules\Exists>
     */
    public static function campusRules(bool $required = true): array
    {
        $visible = self::visibleIds();

        if ($visible === null) {
            return $required
                ? ['required', 'integer', 'exists:campuses,id']
                : ['nullable', 'integer', 'exists:campuses,id'];
        }

        return $required
            ? ['required', 'integer', Rule::in($visible)]
            : ['nullable', 'integer', Rule::in($visible)];
    }
}
