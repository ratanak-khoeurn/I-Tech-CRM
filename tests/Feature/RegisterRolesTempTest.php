<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegisterRolesTempTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => 'student_crm',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
    }

    private function superAdmin(): User
    {
        return User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->firstOrFail();
    }

    public function test_register_page_renders_for_guest(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_register_creates_inactive_viewer_pending_approval(): void
    {
        $email = 'qa.register.' . uniqid() . '@example.test';
        $resp = $this->post(route('register.attempt'), [
            'name' => 'QA Registrant',
            'email' => $email,
            'phone' => '012999888',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $resp->assertRedirect(route('login'));

        $user = User::where('email', $email)->firstOrFail();
        $viewerId = Role::where('name', 'viewer')->value('id');
        $this->assertSame((int) $viewerId, (int) $user->role_id);
        $this->assertFalse((bool) $user->is_active, 'Self-registered account must be inactive pending approval');

        // A pending (inactive) account cannot log in.
        $this->post(route('login.attempt'), ['email' => $email, 'password' => 'secret123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // cleanup
        $user->delete();
    }

    public function test_register_rejects_duplicate_email_and_short_password(): void
    {
        $existing = User::firstOrFail();
        $this->from(route('register'))->post(route('register.attempt'), [
            'name' => 'Dup', 'email' => $existing->email,
            'password' => 'abc', 'password_confirmation' => 'abc',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_roles_pages_render_for_super_admin(): void
    {
        $sa = $this->superAdmin();
        $role = Role::firstOrFail();
        $this->actingAs($sa)->get(route('roles.index'))->assertOk();
        $this->actingAs($sa)->get(route('roles.create'))->assertOk();
        $this->actingAs($sa)->get(route('roles.edit', $role))->assertOk();
    }

    public function test_roles_blocked_for_non_super_admin(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->first();
        if ($admin) {
            $this->actingAs($admin)->get(route('roles.index'))->assertForbidden();
        }
        $viewer = User::whereHas('role', fn ($q) => $q->where('name', 'viewer'))->first();
        if ($viewer) {
            $this->actingAs($viewer)->get(route('roles.index'))->assertForbidden();
        }
        if (!$admin && !$viewer) {
            $this->markTestSkipped('No non-super-admin account present.');
        }
    }
}
