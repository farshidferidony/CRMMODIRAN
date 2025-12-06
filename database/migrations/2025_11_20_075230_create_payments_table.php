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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('pre_invoice_id')->nullable()->constrained('pre_invoices')->nullOnDelete();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 18, 2);
                $table->enum('payment_type', ['cash', 'installment']);
                $table->date('paid_date');
                $table->enum('status', ['pending','confirmed','rejected'])->default('pending');
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
