<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Each fee line is collected through its own channel (tuition by bank, registration
            // by cash), so the method belongs on the line and not only on the payment record.
            $table->string('payment_method', 30)->default('cash')->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
