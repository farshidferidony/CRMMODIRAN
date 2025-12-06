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
        if (!Schema::hasTable('pre_invoices')) {
            Schema::create('pre_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('type', ['normal', 'formal', 'export']); // نوع پیش‌فاکتور
                $table->enum('status', ['draft', 'waiting_purchase', 'approved_manager', 'confirmed', 'rejected', 'closed'])->default('draft');
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->decimal('formal_extra', 18, 2)->nullable(); // مبلغ افزوده رسمی (۱۰٪)
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_invoices');
    }
};
