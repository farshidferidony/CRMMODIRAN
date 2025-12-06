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
        if (!Schema::hasTable('transports')) {
            Schema::create('transports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
                $table->json('product_ids')->nullable(); // محصولات حمل شده (شناسه‌ها به صورت آرایه)
                $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
                $table->string('truck_type', 100)->nullable();
                $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->nullOnDelete();
                $table->enum('status', ['pending', 'collecting', 'transit', 'delivered', 'returned', 'cancelled'])->default('pending');
                $table->decimal('expenses', 15, 2)->nullable(); // هزینه حمل
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
        Schema::dropIfExists('transports');
    }
};
