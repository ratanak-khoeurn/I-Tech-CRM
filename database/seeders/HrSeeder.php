<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        if (Position::count() > 0 || Employee::count() > 0) {
            return;
        }

        $positions = [];
        $defs = [
            ['Director', 'Management', 1],
            ['Admissions Officer', 'Admissions', 2],
            ['Academic Coordinator', 'Academics', 2],
            ['Instructor', 'Academics', 3],
            ['Accountant', 'Finance', 2],
            ['IT Support', 'IT', 3],
        ];
        foreach ($defs as [$title, $dept, $level]) {
            $positions[] = Position::create([
                'title' => $title, 'department' => $dept, 'level' => $level,
                'description' => "$title within the $dept department.", 'is_active' => true,
            ]);
        }

        $campuses = Campus::orderBy('id')->get();
        $users = User::orderBy('id')->get();

        $people = [
            ['Vandet', 'Noun', 'Male', 'hr@itechmediakh.com', '0965828662 / 092426205', 'active', 'full_time'],
            ['Sreyroth', 'Sok', 'Female', 'info@itechmediakh.com', '0965828662 / 092426205', 'active', 'full_time'],
        ];

        foreach ($people as $i => [$first, $last, $gender, $email, $phone, $status, $type]) {
            $emp = new Employee([
                'employee_code' => Employee::generateEmployeeCode(),
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $gender,
                'email' => $email,
                'phone' => $phone,
                'position_id' => $positions[$i % count($positions)]->id,
                'campus_id' => $campuses->isNotEmpty() ? $campuses[$i % $campuses->count()]->id : null,
                'hire_date' => now()->subMonths(6 + $i),
                'employment_type' => $type,
                'status' => $status,
                'salary' => 400 + ($i % 4) * 150,
                'created_by' => $users->first()?->id,
            ]);
            // Link the first few employees to existing login accounts (staff/admin) where email matches by role order.
            if (isset($users[$i]) && $i < 3) {
                $emp->user_id = $users[$i]->id;
            }
            $emp->save();
        }
    }
}
