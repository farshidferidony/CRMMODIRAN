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
        Schema::table('payments', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->after('paid_date');      // تاریخ برنامه‌ریزی‌شده
            $table->date('actual_paid_date')->nullable()->after('scheduled_date'); // تاریخ واقعی پرداخت
            $table->string('receipt_path')->nullable()->after('status');         // فایل فیش
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
