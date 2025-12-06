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
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['individual', 'company', 'both']);
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('passport_number')->nullable();
                $table->string('national_code')->nullable();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->date('birthdate')->nullable();
                $table->string('email')->nullable();
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
        Schema::dropIfExists('customers');
    }
};
