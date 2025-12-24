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
        if (!Schema::hasTable('transport_expenses')) {
            Schema::create('transport_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_id')->constrained()->cascadeOnDelete();
                $table->string('type', 50);               // freight, loading, return, other...
                $table->decimal('amount', 20, 2);
                $table->string('payer', 20)->nullable();  // company/customer
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_expenses');
    }
};
