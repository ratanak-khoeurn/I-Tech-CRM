<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Role;
use App\Models\User;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display all users.
     */
    public function index(Request $request)
    {
        $query = User::with('role')
            ->with('campuses:id,name')
            ->withCount('assignedStudents')
            ->orderBy('name');

        // A campus-scoped admin only sees the accounts that work in their campuses.
        CampusContext::constrainUsers($query);

        return view('settings.users', [
            'users' => $query->paginate(15),
            'roles' => $this->assignableRoles(),
        ]);
    }

    /**
     * Show the form to create a user.
     */
    public function create()
    {
        return view('settings.user-form', [
            'user' => new User(['is_active' => true]),
            'roles' => $this->assignableRoles(),
            'campuses' => CampusContext::manageableCampuses(),
            'selectedCampusIds' => array_map('intval', (array) old('campus_ids', [])),
            'campusBoundRoleIds' => $this->campusBoundRoleIds(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_id' => $this->roleRules(),
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5000',
            ],
            'campus_ids' => ['nullable', 'array'],
            'campus_ids.*' => $this->campusIdRules(),
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['password'] = Hash::make($data['password']);

        // Upload avatar for the new user.
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')
                ->store('users/avatars', 'public');

            if (!$avatarPath) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to upload the avatar.');
            }

            $data['avatar'] = $avatarPath;
        }

        $campusIds = $this->resolveCampusIds($request, (int) $data['role_id']);
        unset($data['campus_ids']);

        $user = User::create($data);

        $user->campuses()->sync($campusIds);

        AuditLog::record(
            'created',
            'user',
            $user->id,
            "Created staff account '{$user->name}'"
        );

        return redirect()
            ->route('users.index')
            ->with('success', "Staff account created for {$user->name}.");
    }

    /**
     * Show the form to edit a user.
     */
    public function edit(User $user)
    {
        abort_unless(CampusContext::allowsManagingUser($user), 403);

        // Read the pivot instead of the relation: the campus scope narrows to the campus
        // currently selected, and an assignment outside it must still render as ticked,
        // or saving this form would silently drop that campus.
        $choices = CampusContext::manageableCampuses();

        return view('settings.user-form', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'campuses' => $choices,
            'selectedCampusIds' => $this->selectedCampusIds($user, $choices),
            'campusBoundRoleIds' => $this->campusBoundRoleIds(),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Campus>  $choices
     * @return list<int>
     */
    private function selectedCampusIds(User $user, $choices): array
    {
        if (old('campus_ids') !== null) {
            return array_map('intval', (array) old('campus_ids'));
        }

        $assigned = array_map(
            fn ($id) => (int) $id,
            DB::table('campus_user')->where('user_id', $user->id)->pluck('campus_id')->all()
        );
        $allowed = array_map(fn ($id) => (int) $id, $choices->pluck('id')->all());

        return array_values(array_intersect($assigned, $allowed));
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        abort_unless(CampusContext::allowsManagingUser($user), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_id' => $this->roleRules(),
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8),
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5000',
            ],
            'campus_ids' => ['nullable', 'array'],
            'campus_ids.*' => $this->campusIdRules(),
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        // Prevent users from changing their own role or deactivating themselves.
        if (
            $user->id === Auth::id()
            && (
                (int) $data['role_id'] !== (int) $user->role_id
                || !$data['is_active']
            )
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'You cannot change your own role or deactivate your own account.'
                );
        }

        // Update password only when a new password is provided.
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Keep the existing avatar unless a new image is uploaded.
        $oldAvatar = $user->avatar;
        $newAvatar = null;

        if ($request->hasFile('avatar')) {
            // Save the new image before deleting the old one.
            $newAvatar = $request->file('avatar')
                ->store('users/avatars', 'public');

            if (!$newAvatar) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to upload the new avatar.');
            }

            $data['avatar'] = $newAvatar;
        } else {
            unset($data['avatar']);
        }

        $campusIds = $this->resolveCampusIds($request, (int) $data['role_id']);
        unset($data['campus_ids']);

        // A campus-scoped admin must not be able to lock themselves out of every campus.
        if ($campusIds === [] && $user->id === Auth::id() && CampusContext::isBounded($user)) {
            return back()
                ->withInput()
                ->with('error', 'Keep at least one campus on your own account.');
        }

        try {
            $user->update($data);
        } catch (\Throwable $exception) {
            // Remove the newly uploaded image if the database update fails.
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        $user->campuses()->sync($campusIds);

        // A campus change for the signed-in user takes effect immediately.
        if ($user->id === Auth::id()) {
            session()->forget(\App\Support\CampusContext::SESSION_KEY);
        }

        // Delete the previous image only after the database update succeeds.
        if (
            $newAvatar
            && $oldAvatar
            && $oldAvatar !== $newAvatar
        ) {
            Storage::disk('public')->delete($oldAvatar);
        }

        AuditLog::record(
            'updated',
            'user',
            $user->id,
            "Updated staff account '{$user->name}'"
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff account updated.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        abort_unless(CampusContext::allowsManagingUser($user), 403);

        // Prevent deleting your own account.
        if ($user->id === Auth::id()) {
            return back()->with(
                'error',
                'You cannot delete your own account.'
            );
        }

        // Prevent deleting the last Super Admin.
        $superAdminRoleId = Role::where('name', 'super_admin')->value('id');

        if (
            $user->isSuperAdmin()
            && $superAdminRoleId
            && User::where('role_id', $superAdminRoleId)
                ->where('id', '!=', $user->id)
                ->count() === 0
        ) {
            return back()->with(
                'error',
                'Cannot delete the last Super Admin.'
            );
        }

        // Unassign students before deleting the user.
        $user->assignedStudents()->update([
            'assigned_staff_id' => null,
        ]);

        $oldAvatar = $user->avatar;

        $user->delete();

        // Delete the avatar after the user is successfully deleted.
        if ($oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        AuditLog::record(
            'deleted',
            'user',
            $user->id,
            "Deleted staff account '{$user->name}'"
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff account deleted.');
    }

    /**
     * Campus selection only applies to the campus-bound roles (admin and staff);
     * other roles are never campus-bound, so any selection for them is discarded.
     *
     * @return list<int>
     */
    private function resolveCampusIds(Request $request, int $roleId): array
    {
        if (!in_array($roleId, $this->campusBoundRoleIds(), true)) {
            return [];
        }

        $ids = collect($request->input('campus_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $visible = CampusContext::assignedIds();

        return $visible === null ? $ids : array_values(array_intersect($ids, $visible));
    }

    /** Roles that can be limited to campuses. */
    private function campusBoundRoleIds(): array
    {
        return array_map(
            fn ($id) => (int) $id,
            Role::whereIn('name', ['admin', 'staff'])->pluck('id')->all()
        );
    }

    /** @return list<Role> */
    private function assignableRoles()
    {
        $allowed = CampusContext::manageableRoleIds();

        return Role::orderBy('id')
            ->when($allowed !== null, fn ($q) => $q->whereIn('id', $allowed))
            ->get();
    }

    private function roleRules(): array
    {
        $allowed = CampusContext::manageableRoleIds();

        return $allowed === null
            ? ['required', 'exists:roles,id']
            : ['required', 'integer', Rule::in($allowed)];
    }

    private function campusIdRules(): array
    {
        $visible = CampusContext::assignedIds();

        return $visible === null
            ? ['integer', 'exists:campuses,id']
            : ['integer', Rule::in($visible)];
    }
}
