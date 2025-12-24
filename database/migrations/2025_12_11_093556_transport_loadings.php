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
        // migration پیشنهادی
        if (!Schema::hasTable('transport_loadings')) {

            Schema::create('transport_loadings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transport_id')->constrained()->cascadeOnDelete();

                // منبع و مسئول خرید
                $table->string('buyer_name')->nullable();         // زهرا یوسفی(خرید)
                $table->string('source_name')->nullable();        // سیمان تهران
                $table->string('phone', 50)->nullable();          // تلفن منبع
                $table->text('address')->nullable();              // آدرس منبع

                // مشخصات ردیف
                $table->unsignedInteger('priority')->default(1);  // اولویت بارگیری
                $table->dateTime('delivery_time')->nullable();    // تاریخ تحویل
                $table->string('voucher_row', 50)->nullable();    // ردیف/حواله

                // جمع کالاهای این آدرس (برای گزارش و محاسبه سریع)
                $table->decimal('total_value_with_insurance', 20, 2)->nullable();
                $table->decimal('total_weight', 15, 3)->nullable();
                $table->unsignedBigInteger('total_quantity')->nullable();

                $table->timestamps();
            });



            // Schema::create('transport_loadings', function (Blueprint $table) {
            //     $table->id();
            //     $table->foreignId('transport_id')->constrained()->cascadeOnDelete();
            //     $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            //     $table->unsignedInteger('priority')->default(1);   // ترتیب
            //     $table->string('source_name')->nullable();         // نام انبار/کارخانه
            //     $table->string('phone', 50)->nullable();
            //     $table->text('address')->nullable();
            //     $table->dateTime('delivery_time')->nullable();     // زمان تقریبی تحویل/آماده بارگیری
            //     $table->string('voucher_row', 50)->nullable();     // شماره ردیف حواله/فاکتور
            //     $table->decimal('value_with_insurance', 20, 2)->nullable(); // ارزش ردیف
            //     $table->decimal('weight', 15, 3)->nullable();      // وزن تقریبی
            //     $table->timestamps();
            // });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_loadings');
    }
};
