<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // One fee may take a flat rebate while another takes a percentage, so the unit the
            // cashier typed belongs to the line; `discount` always stays in dollars.
            $table->string('discount_type', 10)->default('amount')->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });
    }
};
