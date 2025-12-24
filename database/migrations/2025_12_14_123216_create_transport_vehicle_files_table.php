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
        if (!Schema::hasTable('transport_vehicle_files')) {
            Schema::create('transport_vehicle_files', function (Blueprint $table) {
                $table->id();

                $table->foreignId('transport_vehicle_id')
                    ->constrained('transport_vehicles')
                    ->cascadeOnDelete();

                $table->string('title');  // توضیح فایل
                $table->string('path');   // مسیر فایل در storage

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
        Schema::dropIfExists('transport_vehicle_files');
    }
};
