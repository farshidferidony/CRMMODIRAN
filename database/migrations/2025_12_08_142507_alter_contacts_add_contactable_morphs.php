<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // اگر قبلاً ستون‌های قدیمی مثل customer_id یا company_id داری، اول آن‌ها را حذف کن
            // $table->dropColumn(['customer_id', 'company_id']); // فقط اگر واقعاً وجود دارند

            // اضافه کردن ستون‌های polymorphic
            $table->unsignedBigInteger('contactable_id')->nullable()->after('id');
            $table->string('contactable_type')->nullable()->after('contactable_id');

            // ایندکس ترکیبی مشابه morphs (اختیاری ولی بهتر است)
            $table->index(['contactable_type', 'contactable_id'], 'contacts_contactable_index');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_contactable_index');
            $table->dropColumn(['contactable_type', 'contactable_id']);
        });
    }
};
