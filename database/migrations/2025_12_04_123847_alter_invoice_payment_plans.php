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
        Schema::table('invoice_payment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_payment_plans', 'pre_invoice_id')) {
                $table->foreignId('pre_invoice_id')->nullable()
                    ->after('invoice_id')
                    ->constrained('pre_invoices')
                    ->nullOnDelete();
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payment_plans', function (Blueprint $table) {
            //
        });
    }
};
