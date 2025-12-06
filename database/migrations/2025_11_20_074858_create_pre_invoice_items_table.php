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
        if (!Schema::hasTable('pre_invoice_items')) {
            Schema::create('pre_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pre_invoice_id')->constrained('pre_invoices')->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->json('attributes')->nullable(); // خصوصیات انتخابی هر آیتم
                $table->decimal('total', 18, 2)->default(0);
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_invoice_items');
    }
};
