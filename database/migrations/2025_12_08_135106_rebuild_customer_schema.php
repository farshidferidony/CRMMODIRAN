<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // اگر پروژه تازه است و جداول قدیمی customers/companies را نمی‌خواهی:
        Schema::dropIfExists('company_customer_roles');
        Schema::dropIfExists('customer_links');
        Schema::dropIfExists('persons');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('customers');

        // جدول مرکزی مشتری
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->enum('source', [
                'website',
                'instagram',
                'telegram',
                'business_partners',
                'phone_marketing',
                'from_employees',
                'from_customers',
                'word_of_mouth',
                'public_relations',
                'seminar',
                'conference',
                'exhibition',
                'mass_advertising',
                'email_marketing',
                'sms_marketing',
                'fax_marketing',
                'direct_contact',
            ])->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creator_id')->references('id')->on('users')->nullOnDelete();
        });

        // جدول شخص
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

        // جدول شرکت
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('economic_code')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // لینک customer ↔ شخص/شرکت (polymorphic)
        Schema::create('customer_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->morphs('linkable'); // linkable_type, linkable_id
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        // نقش کارمند در شرکت
        Schema::create('company_customer_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('person_id');
            $table->string('role')->nullable(); // sales_expert, purchase_expert, ...

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
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
