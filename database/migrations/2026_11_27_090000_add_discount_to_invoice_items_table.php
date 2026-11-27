<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Discounts are negotiated per fee (tuition gets one, a replacement card does not),
            // so the reduction belongs on the line instead of being shared out at print time.
            $table->decimal('discount', 10, 2)->default(0)->after('payment_method');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }

    /** Spread each invoice-level discount over its lines so existing paper invoices keep their total. */
    private function backfill(): void
    {
        $invoices = DB::table('invoices')->where('discount', '>', 0)->get(['id', 'discount']);

        foreach ($invoices as $invoice) {
            $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->orderBy('id')->get(['id', 'amount']);
            $subtotal = (float) $items->sum('amount');
            if ($subtotal <= 0 || $items->isEmpty()) {
                continue;
            }

            $discount = min((float) $invoice->discount, $subtotal);
            $given = 0.0;
            $last = $items->last();

            foreach ($items as $item) {
                if ((int) $item->id === (int) $last->id) {
                    // The tail absorbs the rounding residue so the lines still sum to the invoice discount.
                    DB::table('invoice_items')->where('id', $item->id)->update(['discount' => round($discount - $given, 2)]);
                    break;
                }
                $share = round($discount * ((float) $item->amount / $subtotal), 2);
                $given += $share;
                DB::table('invoice_items')->where('id', $item->id)->update(['discount' => $share]);
            }
        }
    }
};
