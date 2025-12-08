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
        Schema::table('pre_invoices', function (Blueprint $table) {
            // برای پیش‌فاکتور خرید
            $table->boolean('supplier_payment_approved')->default(false);
            $table->enum('purchase_status', ['pending', 'purchased', 'final_approved'])
                ->default('pending');

            // برای پیش‌فاکتور فروش
            $table->enum('sale_flow_status', [
                'default',
                'ready_for_purchase',
                'purchase_completed',
                'ready_for_transport_request',
            ])->default('default');
        });

        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->decimal('final_purchase_weight', 15, 3)->nullable();
            $table->enum('purchase_status', ['pending', 'purchased'])
                ->default('pending');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_invoices', function (Blueprint $table) {
            //
        });
    }
};
