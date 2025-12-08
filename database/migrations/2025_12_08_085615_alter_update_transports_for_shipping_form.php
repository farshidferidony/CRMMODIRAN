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
        
        Schema::table('transports', function (Blueprint $table) {
            $table->unsignedBigInteger('pre_invoice_id')->nullable()->after('invoice_id');
            $table->enum('status', [
                'requested_by_sales',        // درخواست ثبت شده توسط کارشناس فروش (فرم اولیه پر شده)
                'completed_by_sales',        // فرم توسط کارشناس فروش تایید شده
                'completed_by_purchase',     // آدرس‌های بارگیری توسط خرید تکمیل و تایید شده
                'assigned_to_logistics',     // مدیر لجستیک کارشناس حمل را تعیین کرده
                'in_progress',               // کارشناس حمل در حال انجام مراحل جستجوی ماشین/بارگیری/ارسال است
                'waiting_accounting_approval', // ارسال برای حسابداری
                'approved_by_accounting',    // تایید حسابداری
                'approved_by_sales_manager', // تایید مدیر فروش
                'closed'                     // حمل نهایی شده و بار تحویل داده شده
            ])->default('requested_by_sales')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            //
        });
    }
};
