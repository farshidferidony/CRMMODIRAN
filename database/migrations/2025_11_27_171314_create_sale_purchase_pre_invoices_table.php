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
    //     Schema::create('sale_purchase_pre_invoices', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }

    public function up(): void
    {
        Schema::create('sale_purchase_pre_invoices', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();

            // پیش‌فاکتور فروش اصلی
            $table->foreignId('sale_pre_invoice_id')
                ->constrained('pre_invoices')
                ->onDelete('cascade');

            // پیش‌فاکتور خرید مرتبط
            $table->foreignId('purchase_pre_invoice_id')
                ->constrained('pre_invoices')
                ->onDelete('cascade');

            // منبعی که این خرید از آن انجام می‌شود
            $table->foreignId('source_id')
                ->constrained('sources')
                ->onDelete('cascade');

            // ایجادکنندهٔ این لینک (معمولاً کارشناس/مدیر خرید)
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            // برای آینده اگر خواستی وضعیت خود این رابطه را هم داشته باشی
            $table->string('status', 50)->default('active');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_purchase_pre_invoices');
    }
};
