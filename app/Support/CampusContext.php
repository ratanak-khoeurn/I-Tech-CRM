<?php

namespace App\Support;

use App\Models\Campus;
use App\Models\Invoice;
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
 * The session "campus_id" value is a drill-down inside that boundary. It never grants
 * access to a campus the account is not assigned to, but while it points at one campus
 * every read is narrowed to it, so a page shows a single campus instead of a mix.
 */
class CampusContext
{
    public const SESSION_KEY = 'campus_id';

    public const ALL = 'all';

    /** @var array<int|string, list<int>|null> */
    private static array $assignedCache = [];

    /**
     * The campuses this account may work in, or null when it may work in all of them.
     *
     * This is the security boundary: it only depends on the pivot, never on the
     * campus picked in the switcher, so authorising a write cannot be widened or
     * narrowed by a session value.
     *
     * Reads the pivot with the query builder on purpose: User::campusIds() goes
     * through the campuses relation, and the Campus model carries a global scope that
     * calls back into this method, so the relation would recurse into itself.
     *
     * @return list<int>|null
     */
    public static function assignedIds(?User $user = null): ?array
    {
        $user = $user ?: (Auth::check() ? Auth::user() : null);
        $key = $user ? $user->id : 'guest';

        if (array_key_exists($key, self::$assignedCache)) {
            return self::$assignedCache[$key];
        }

        if (!$user || !$user->hasRole('super_admin', 'admin', 'staff') || $user->isSuperAdmin()) {
            return self::$assignedCache[$key] = null;
        }

        return self::$assignedCache[$key] = array_map(
            fn ($id) => (int) $id,
            DB::table('campus_user')->where('user_id', $user->id)->pluck('campus_id')->all()
        );
    }

    /**
     * The campus set every read path must use: the boundary, narrowed to the campus
     * currently selected in the switcher. null still means unrestricted.
     *
     * @return list<int>|null
     */
    public static function visibleIds(?User $user = null): ?array
    {
        $assigned = self::assignedIds($user);
        $user = $user ?: (Auth::check() ? Auth::user() : null);

        if (!$user) {
            return $assigned;
        }

        $selected = self::current($user);

        if ($selected === null) {
            return $assigned;
        }

        return $assigned === null || in_array($selected, $assigned, true) ? [$selected] : $assigned;
    }

    /** Whether this account is limited to a set of campuses. */
    public static function isBounded(?User $user = null): bool
    {
        return self::assignedIds($user) !== null;
    }

    /** Whether this account may touch data of one campus (null = unassigned data). */
    public static function allows(?int $campusId, ?User $user = null): bool
    {
        $assigned = self::assignedIds($user);

        return $assigned === null || ($campusId !== null && in_array($campusId, $assigned, true));
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

    /**
     * Apply the boundary through the invoice a money record belongs to, so a payment
     * follows the campus of the student it was collected from.
     *
     * @template T of Builder
     * @param  T  $query
     * @return T
     */
    public static function whereVisibleInvoice(Builder $query, string $column = 'invoice_id'): Builder
    {
        if (self::visibleIds() === null) {
            return $query;
        }

        $query->whereIn($column, Invoice::query()->select('id'));

        return $query;
    }

    /** Campus ids this user may pick from in a switcher, login form or filter. */
    public static function assignable(User $user): array
    {
        if (!self::managesData($user)) {
            return [];
        }

        $assigned = self::assignedIds($user);

        if ($assigned === null) {
            return array_map(fn ($id) => (int) $id, DB::table('campuses')->orderBy('name')->pluck('id')->all());
        }

        return $assigned;
    }

    /**
     * Campus rows to offer in a switcher or a staff-account form.
     *
     * Deliberately ignores the drill-down selection: without every assigned campus in
     * the list, picking one would make the other ones unreachable.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Campus>
     */
    public static function manageableCampuses(?User $user = null, bool $activeOnly = false)
    {
        $user = $user ?: (Auth::check() ? Auth::user() : null);
        $ids = $user ? self::assignable($user) : null;

        return Campus::withoutGlobalScopes()
            ->when($ids !== null, fn ($query) => $query->whereIn('id', $ids))
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();
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
        return self::assignedIds($user) === null ? null : ($ids[0] ?? null);
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
        if (self::assignedIds($user) === null) {
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
     * Uses the assignment boundary, not the selected campus, so drilling into one
     * campus never hides the other accounts this admin still has to look after.
     *
     * @template T of Builder
     * @param  T  $query
     * @return T
     */
    public static function constrainUsers(Builder $query): Builder
    {
        $assigned = self::assignedIds();

        if ($assigned === null) {
            return $query;
        }

        $query->whereIn('role_id', self::manageableRoleIds())
            ->whereIn('id', function ($sub) use ($assigned) {
                $sub->select('user_id')->from('campus_user')->whereIn('campus_id', $assigned);
            });

        return $query;
    }

    /** Whether the signed-in account may edit or delete this account. */
    public static function allowsManagingUser(User $target): bool
    {
        $assigned = self::assignedIds();

        if ($assigned === null) {
            return true;
        }

        $roles = self::manageableRoleIds();

        if ($target->role_id === null || !in_array((int) $target->role_id, $roles, true)) {
            return false;
        }

        return DB::table('campus_user')
            ->where('user_id', $target->id)
            ->whereIn('campus_id', $assigned)
            ->exists();
    }

    /**
     * The campus a list page should filter by.
     *
     * When the account is narrowed to one campus that campus is always the filter, so a
     * hand-written query string cannot widen the page. Otherwise an explicit selection
     * inside the boundary wins, and anything else falls back to the active campus.
     */
    public static function filterFor(Request $request): ?int
    {
        $user = $request->user() ?? Auth::user();
        $visible = self::visibleIds($user);

        if ($visible === null || count($visible) > 1) {
            if ($request->has(self::SESSION_KEY)) {
                $value = $request->input(self::SESSION_KEY);

                if ($value === self::ALL) {
                    return null;
                }

                if (is_numeric($value) && self::allows((int) $value)) {
                    return (int) $value;
                }
            }
        }

        return $user ? self::current($user) : null;
    }

    /**
     * Validation rules for a campus_id the account is allowed to write.
     *
     * @return list<string|\Illuminate\Contracts\Validation\Rule|\Illuminate\Validation\Rules\Exists>
     */
    public static function campusRules(bool $required = true): array
    {
        $assigned = self::assignedIds();

        if ($assigned === null) {
            return $required
                ? ['required', 'integer', 'exists:campuses,id']
                : ['nullable', 'integer', 'exists:campuses,id'];
        }

        return $required
            ? ['required', 'integer', Rule::in($assigned)]
            : ['nullable', 'integer', Rule::in($assigned)];
    }
}
