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
    //     Schema::create('purchase_assignments', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }

    public function up(): void
    {
        if (!Schema::hasTable('purchase_assignments')) {
            Schema::create('purchase_assignments', function (Illuminate\Database\Schema\Blueprint $table) {
                $table->id();

                // آیتم پیش‌فاکتور فروش
                $table->foreignId('pre_invoice_item_id')
                    ->constrained('pre_invoice_items')
                    ->onDelete('cascade');

                // کارشناس خرید مسئول این آیتم
                $table->foreignId('buyer_id')
                    ->constrained('users')
                    ->onDelete('cascade');

                // منبع پیشنهادی (در ابتدا می‌تواند null باشد و بعداً توسط کارشناس خرید یا مدیر خرید ست شود)
                $table->foreignId('source_id')
                    ->nullable()
                    ->constrained('sources')
                    ->nullOnDelete();

                // وضعیت ارجاع از نظر خرید
                // assigned = ارجاع شده، pricing = در حال قیمت‌گذاری،
                // priced = قیمت ثبت شده، approved_by_buyer = خود کارشناس OK،
                // rejected = رد شده، cancelled = لغو (مثلاً تغییر منبع)
                $table->enum('status', [
                    'assigned',
                    'pricing',
                    'priced',
                    'approved_by_buyer',
                    'rejected',
                    'cancelled',
                ])->default('assigned');

                // قیمت خرید پیشنهادی برای همین assignment (برای همین منبع)
                $table->decimal('unit_price', 15, 2)->nullable();

                // توضیح/علت رد یا توضیحات تغییر
                $table->text('note')->nullable();

                // کسی که این ارجاع را ایجاد کرده (معمولاً مدیر خرید)
                $table->foreignId('created_by')
                    ->constrained('users')
                    ->onDelete('cascade');

                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_assignments');
    }
};
