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
        if (!Schema::hasTable('shipping_form_pickups')) {
            Schema::create('shipping_form_pickups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transport_id');    // ارتباط با فرم حمل
                $table->unsignedBigInteger('purchase_item_id')->nullable(); // آیتم خرید انتخاب‌شده
                $table->unsignedBigInteger('purchase_expert_id')->nullable(); // کارشناس خرید مسئول

                // بخش اول: محل بارگیری
                $table->string('priority');        // اولویت بارگیری
                $table->string('phone');
                $table->text('address');
                $table->dateTime('delivery_time'); // زمان تحویل
                $table->string('voucher_row');     // ردیف حواله

                // بخش دوم: اطلاعات کالا (جمع ارزش + وزن تقریبی)
                $table->decimal('goods_value_with_insurance', 18, 2)->nullable();
                $table->decimal('approx_weight', 18, 3)->nullable();

                $table->boolean('approved_by_purchase')->default(false);

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('transport_id')->references('id')->on('transports')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_form_pickups');
    }
};
