<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     if (!Schema::hasTable('sources')) {
    //         Schema::create('sources', function (Blueprint $table) {
    //             $table->id();
    //             $table->string('name');
    //             $table->enum('type', ['company', 'individual']);
    //             $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
    //             $table->string('country', 100)->nullable();
    //             $table->string('province', 100)->nullable();
    //             $table->string('city', 100)->nullable();
    //             $table->timestamps();
    //             $table->softDeletes();
    //         });
    //     }
    // }

    // php artisan make:model Source -m

    public function up(): void
    {
        if (!Schema::hasTable('sources')) {
            Schema::create('sources', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['individual', 'company', 'both'])->default('individual');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('passport_number', 50)->nullable()->unique();
                $table->string('national_code', 20)->nullable()->unique();
                $table->date('birthdate')->nullable();
                $table->string('email', 190)->nullable()->unique();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
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
        Schema::dropIfExists('sources');
    }
};
