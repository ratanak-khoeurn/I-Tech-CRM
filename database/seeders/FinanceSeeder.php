<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        if (FeeStructure::count() > 0) {
            return;
        }
        $courses = Course::orderBy('id')->get();
        if ($courses->isEmpty()) {
            return;
        }

        $fees = [];
        foreach ($courses as $i => $course) {
            $fees[] = FeeStructure::create([
                'course_id' => $course->id,
                'name' => 'Tuition — ' . $course->name,
                'fee_type' => 'tuition',
                'amount' => 150 + ($i % 4) * 50,
                'frequency' => 'per_semester',
                'is_active' => true,
            ]);
        }
        $fees[] = FeeStructure::create([
            'course_id' => $courses->first()->id,
            'name' => 'Registration — ' . $courses->first()->name,
            'fee_type' => 'registration',
            'amount' => 25,
            'frequency' => 'one_time',
            'is_active' => true,
        ]);

        $students = Student::where('record_status', 'active')->orderBy('id')->limit(6)->get();
        $patterns = ['paid', 'partial', 'sent', 'sent', 'partial', 'paid'];
        foreach ($students as $i => $student) {
            $fee = $fees[$i % count($fees)];
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . now()->format('Y') . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'fee_structure_id' => $fee->id,
                'issue_date' => now()->subDays(30 - $i * 3),
                'due_date' => now()->addDays(10 + $i),
                'status' => 'sent',
                'discount' => 0,
                'notes' => 'Seeded demo invoice.',
                'created_by' => 1,
            ]);
            $invoice->items()->create([
                'description' => $fee->name,
                'quantity' => 1,
                'unit_price' => $fee->amount,
                'amount' => $fee->amount,
            ]);

            $pattern = $patterns[$i % count($patterns)];
            if ($pattern === 'paid') {
                $invoice->payments()->create([
                    'payment_date' => now()->subDays(20),
                    'amount' => $invoice->total(),
                    'method' => 'cash',
                    'reference' => 'RC-' . (1000 + $i),
                    'received_by' => 1,
                ]);
            } elseif ($pattern === 'partial') {
                $invoice->payments()->create([
                    'payment_date' => now()->subDays(15),
                    'amount' => round($invoice->total() / 2, 2),
                    'method' => 'bank_transfer',
                    'reference' => 'TR-' . (2000 + $i),
                    'received_by' => 1,
                ]);
            }
            $invoice->syncStatus();
        }
    }
}
