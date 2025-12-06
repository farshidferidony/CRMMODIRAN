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
        if (!Schema::hasTable('reward_penalties')) {
            Schema::create('reward_penalties', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('type', ['reward', 'penalty']);
                $table->enum('source', ['sale', 'purchase']);
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_penalties');
    }
};
