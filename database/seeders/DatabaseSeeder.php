<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,
            // SampleDataSeeder::class,
            FinanceSeeder::class,
            AcademicSeeder::class,
            HrSeeder::class,
        ]);
    }
}
