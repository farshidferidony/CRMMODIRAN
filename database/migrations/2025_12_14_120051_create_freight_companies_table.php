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
        if (!Schema::hasTable('freight_companies')) {
            Schema::create('freight_companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');                 // نام شرکت باربری
                $table->string('national_id')->nullable(); // شناسه/کد اقتصادی (در صورت نیاز)
                $table->string('phone')->nullable();
                $table->string('mobile')->nullable();
                $table->string('address')->nullable();
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
        Schema::dropIfExists('freight_companies');
    }
};
