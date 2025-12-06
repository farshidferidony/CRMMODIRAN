<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['normal', 'formal', 'export']);
                $table->enum('status', ['draft', 'awaiting_payment', 'paid', 'closed', 'rejected'])->default('draft');
                $table->decimal('total_amount', 18, 2);
                $table->decimal('formal_extra', 18, 2)->nullable(); // مبلغ اضافه رسمی
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
        Schema::dropIfExists('invoices');
    }
};
