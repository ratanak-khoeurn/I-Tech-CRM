<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Course;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Admin', 'description' => 'Full access to all system features and settings.'],
            ['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Manage students, staff, communication, and reports.'],
            ['name' => 'staff', 'display_name' => 'Staff', 'description' => 'View and update assigned student records and communication history.'],
            ['name' => 'student', 'display_name' => 'Student', 'description' => 'Read-only access to permitted student information.'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }

        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@itechmediakh.com', 'role' => 'super_admin', 'phone' => '012345678'],
            ['name' => 'Kakada Horn', 'email' => 'h.kakada@itechmediakh.com', 'role' => 'super_admin', 'phone' => '012345672'],
            ['name' => 'Ratanak Khoeurn', 'email' => 'k.ratanak@itechmediakh.com', 'role' => 'super_admin', 'phone' => '012345679'],
            ['name' => 'Si Ey Sor', 'email' => 's.siey@itechmediakh.com', 'role' => 'staff', 'phone' => '012345680'],
            ['name' => 'Sereykosal Ken', 'email' => 'k.sereykosal@itechmediakh.com', 'role' => 'staff', 'phone' => '012345681'],
            ['name' => 'Student', 'email' => 'student@itechmediakh.com', 'role' => 'student', 'phone' => '012345682'],
        ];
        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], [
                'name' => $u['name'],
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', $u['role'])->value('id'),
                'phone' => $u['phone'],
                'is_active' => true,
            ]);
        }

        $campuses = [
            ['name' => 'Toul Tompong Main Campus', 'code' => 'PP', 'address' => 'St. 470, Sangkat Chomkar Mon1, Toul Tompong1, Phnom Penh, Cambodia', 'phone' => '023456789'],
            ['name' => 'Chbar Ampov Campus', 'code' => 'CBA', 'address' => 'St. 470, Sangkat Chbar Ampov, Chbar Ampov2, Phnom Penh, Cambodia', 'phone' => '023456790'],
            ['name' => 'Reusey Keo Campus', 'code' => 'RK', 'address' => 'St. 470, Sangkat Reusey Keo, Reusey Keo2, Phnom Penh, Cambodia', 'phone' => '023456791'],


        ];
        foreach ($campuses as $c) {
            Campus::firstOrCreate(['code' => $c['code']], $c);
        }

        $courses = [
            ['name' => 'Microsoft Office', 'code' => 'OFF', 'description' => 'Introduction to Microsoft Office suite', 'duration_months' => 2],
            ['name' => 'Graphic Design', 'code' => 'GD', 'description' => 'Visual and graphic design program', 'duration_months' => 2],
            ['name' => 'Photography', 'code' => 'PHO', 'description' => 'Introduction to photography techniques', 'duration_months' => 2],
            ['name' => 'Video Editing', 'code' => 'VID', 'description' => 'Introduction to video editing software', 'duration_months' => 2],
            ['name' => 'Robotics', 'code' => 'ROB', 'description' => 'Introduction to robotics and automation', 'duration_months' => 3],
            ['name' => 'Artificial Intelligence', 'code' => 'AI', 'description' => 'Introduction to artificial intelligence concepts', 'duration_months' => 3],



        ];
        foreach ($courses as $c) {
            Course::firstOrCreate(['code' => $c['code']], $c);
        }

        $settings = [
            ['key' => 'center_name', 'value' => 'I-Tech Media Center', 'group' => 'general'],
            ['key' => 'center_email', 'value' => 'info@itechmediakh.com', 'group' => 'general'],
            ['key' => 'center_phone', 'value' => '092 426 205', 'group' => 'general'],
            ['key' => 'center_address', 'value' => 'Building #10A, 1st Floor, St. 470, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh', 'group' => 'general'],
            ['key' => 'notify_followup', 'value' => '1', 'group' => 'notifications'],
            ['key' => 'notify_overdue', 'value' => '1', 'group' => 'notifications'],
            ['key' => 'notify_events', 'value' => '1', 'group' => 'notifications'],
            ['key' => 'notify_opportunities', 'value' => '1', 'group' => 'notifications'],
        ];
        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}
