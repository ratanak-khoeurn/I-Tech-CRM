<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Changing the riel rate in Settings must not rewrite money that has already changed hands,
        // so each document keeps the rate of its own day: an invoice at the time it was issued, a
        // payment at the time it was collected. Null means "older than this column", in which case
        // the figure falls back to the rate currently saved.
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('exchange_rate', 10, 2)->nullable()->after('discount');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('exchange_rate', 10, 2)->nullable()->after('amount');
        });

        // Nothing was billed before this column existed except under the rate saved today, so
        // freeze every existing document at it: the figures they show now are what they will keep.
        $current = (float) DB::table('settings')->where('key', 'riel_rate')->value('value');
        if ($current > 0) {
            DB::table('invoices')->whereNull('exchange_rate')->update(['exchange_rate' => $current]);
            DB::table('payments')->whereNull('exchange_rate')->update(['exchange_rate' => $current]);
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });
    }
};
