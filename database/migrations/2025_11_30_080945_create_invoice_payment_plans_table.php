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
        if (!Schema::hasTable('invoice_payment_plans')) {
            Schema::create('invoice_payment_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 18, 2);          // مبلغ این قسط
                $table->enum('payment_type',['cash','installment']);
                $table->date('scheduled_date');            // تاریخی که قرار است واریز شود
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_plans');
    }
};
