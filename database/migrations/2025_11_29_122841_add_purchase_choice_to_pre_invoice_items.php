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
        // if (!Schema::hasTable('purchase_assignments')) {
        Schema::table('pre_invoice_items', function (Illuminate\Database\Schema\Blueprint $table) {
            // assignment انتخاب‌شدهٔ نهایی
            $table->foreignId('chosen_purchase_assignment_id')
                ->nullable()
                ->after('total')
                ->constrained('purchase_assignments')
                ->nullOnDelete();

            // قیمت خرید نهایی به ازای واحد (کپی از assignment انتخابی)
            $table->decimal('purchase_unit_price', 15, 2)
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
