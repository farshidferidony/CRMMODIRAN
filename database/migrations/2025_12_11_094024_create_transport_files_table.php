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
        if (!Schema::hasTable('transport_files')) {
            Schema::create('transport_loading_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_loading_id')->constrained()->cascadeOnDelete();
                $table->string('title');        // مثل "عکس بار"، "فایل باسکول"، "فایل خرج بنگاه"
                $table->string('path');         // مسیر فایل
                $table->timestamps();
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_files');
    }
};
