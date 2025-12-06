<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        // if (!Schema::hasTable('pre_invoice_items')) {
        Schema::table('pre_invoice_items', function (Illuminate\Database\Schema\Blueprint $table) {
            // قیمت فروش واحد (روی خود آیتم پیش‌فاکتور فروش)
            $table->decimal('sale_unit_price', 15, 2)
                ->nullable()
                ->after('chosen_purchase_assignment_id');
        });
        // }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            //
        });
    }
};
