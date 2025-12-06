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
                $table->foreignId('pre_invoice_id')->constrained('pre_invoices')->cascadeOnDelete();
                $table->enum('direction', ['customer','supplier']); // فعلا customer
                $table->decimal('amount', 18, 2);
                $table->enum('type', ['full','prepayment','installment']);
                $table->string('method')->nullable();      // کارت، حواله، نقد...
                $table->string('reference')->nullable();   // شماره تراکنش/چک
                $table->dateTime('paid_at')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('approved_by_finance')->default(false);
                $table->foreignId('finance_approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('finance_reject_reason')->nullable();
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
