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
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                // $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');
                $table->enum('type', ['mobile', 'phone', 'fax', 'email']);
                $table->string('value');
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
