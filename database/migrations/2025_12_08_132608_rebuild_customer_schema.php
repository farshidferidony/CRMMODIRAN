<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
         * اگر پروژه تازه است و داده‌ای نداری، می‌توانی جداول قبلی را حذف کنی.
         * اگر داده داری، قبل از اجرای این مایگریشن حتماً بک‌آپ بگیر.
         */
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
         
        // در صورت وجود، جداول قدیمی را حذف کن (بر اساس نیاز خودت)
        Schema::dropIfExists('company_customer_roles');
        Schema::dropIfExists('customer_links');
        Schema::dropIfExists('persons');

        // اگر می‌خواهی customers و companies فعلی را هم از صفر بسازی:
        Schema::dropIfExists('customers');
        Schema::dropIfExists('companies');

        // 1) جدول customers (هسته مشتری)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // نوع آشنایی (source)
            $table->enum('source', [
                'website',
                'instagram',
                'telegram',
                'business_partners',     // شرکای تجاری
                'phone_marketing',       // بازاریابی تلفنی
                'from_employees',        // از طریق کارمندان
                'from_customers',        // از طریق مشتریان
                'word_of_mouth',         // بازاریابی دهان به دهان
                'public_relations',      // روابط عمومی
                'seminar',               // سمینار
                'conference',            // همایش
                'exhibition',            // نمایشگاه
                'mass_advertising',      // تبلیغات انبوه
                'email_marketing',       // ایمیل مارکتینگ
                'sms_marketing',         // اس ام اس مارکتینگ
                'fax_marketing',         // فکس مارکتینگ
                'direct_contact',        // ارتباط مستقیم
            ])->nullable();

            // فعال بودن / عدم فعالیت مشتری
            $table->boolean('is_active')->default(true);

            // کاربر ایجادکننده
            $table->unsignedBigInteger('creator_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // 2) جدول persons (اطلاعات شخصی)
        Schema::create('persons', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('national_code', 20)->nullable();
            $table->string('passport_number', 50)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 3) جدول companies (اطلاعات حقوقی)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('economic_code')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 4) جدول رابط customer_links بین customer و شخص/شرکت
        Schema::create('customer_links', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id');

            // linkable_type (مثلاً App\Models\Person یا App\Models\Company)
            // linkable_id
            $table->morphs('linkable');

            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });

        // 5) جدول company_customer_roles (کارمند–شرکت–نقش)
        Schema::create('company_customer_roles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('person_id'); // کارمند (شخص)
            $table->string('role')->nullable();      // مثل: purchase_expert, sales_expert, ...

            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('person_id')
                ->references('id')
                ->on('persons')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_customer_roles');
        Schema::dropIfExists('customer_links');
        Schema::dropIfExists('persons');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('customers');
    }
};
