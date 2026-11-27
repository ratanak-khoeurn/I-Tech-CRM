<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // A fee may genuinely be quoted in riel (materials, an exam card) while tuition is billed in
            // dollars, so the currency belongs to the line. `unit_price`, `discount` and `amount` always
            // stay in US dollars; these three columns remember what the cashier actually typed.
            $table->string('currency', 3)->default('USD')->after('description');
            $table->decimal('entered_unit_price', 12, 2)->nullable()->after('unit_price');
            $table->decimal('entered_discount', 12, 2)->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['currency', 'entered_unit_price', 'entered_discount']);
        });
    }
};
