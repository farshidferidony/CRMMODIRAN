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
            Schema::create('transport_loading_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_loading_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();

                $table->decimal('quantity', 15, 3);        // مقدار انتخابی برای این آدرس
                $table->string('unit', 50)->nullable();    // کیلوگرم
                $table->decimal('unit_price', 20, 2);      // قیمت واحد
                $table->decimal('value_with_insurance', 20, 2); // مبلغ این آیتم + بیمه

                // برای enforce اینکه همه آیتم‌های یک loading از یک منبع هستند،
                // منبع واقعی محصول را در product یا جدول دیگری نگه می‌داری و در کد چک می‌کنی.
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_loading_items');
    }
};
