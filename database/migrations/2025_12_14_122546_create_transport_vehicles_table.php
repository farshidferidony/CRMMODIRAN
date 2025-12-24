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
        if (!Schema::hasTable('transport_vehicles')) {
            Schema::create('transport_vehicles', function (Blueprint $table) {

                $table->id();

                $table->foreignId('transport_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->boolean('is_wagon')->default(false); // 0=ماشین، 1=واگن

                // شرکت باربری (اختیاری)
                $table->foreignId('freight_company_id')
                    ->nullable()
                    ->constrained('freight_companies')
                    ->nullOnDelete();

                $table->string('freight_company_name')->nullable(); // اگر شرکت به صورت متن آزاد وارد شد

                // نوع وسیله (ماشین/واگن)
                $table->string('vehicle_type')->nullable(); // مثلاً truck_flat, truck_ten_wheeler, wagon_normal, wagon_russian ...

                // وضعیت وسیله (enum متنی مشترک برای ماشین و واگن)
                $table->string('status')->default('searching'); 
                // مثال: searching, reserved, loading, on_the_way, arrived, unloading, done

                // اطلاعات راننده / واگن
                $table->string('driver_name')->nullable();
                $table->string('driver_national_code')->nullable();
                $table->string('driver_mobile')->nullable();
                $table->string('driver_helper')->nullable();

                // پلاک ماشین (برای واگن می‌تواند خالی باشد یا برای لکوموتیو استفاده شود)
                $table->string('plate_iran', 4)->nullable();     // 11
                $table->string('plate_3digit', 3)->nullable();   // 922
                $table->string('plate_letter', 2)->nullable();   // ع
                $table->string('plate_2digit', 2)->nullable();   // 74

                // شماره بارنامه / واگن‌نامه
                $table->string('bill_of_lading_number')->nullable();

                // تاریخ‌های برنامه‌ریزی‌شده و واقعی (میلادی در DB، ورودی جلالی در UI)
                $table->timestamp('planned_loading_at')->nullable(); // تاریخ حمل (برنامه‌ریزی‌شده)
                $table->timestamp('actual_loading_at')->nullable();  // زمان واقعی بارگیری
                $table->timestamp('arrival_at')->nullable();         // زمان رسیدن به مقصد
                $table->timestamp('unloading_at')->nullable();       // زمان تخلیه

                // هزینه‌ها
                $table->unsignedBigInteger('total_freight_amount')->nullable(); // کرایه کل
                $table->unsignedBigInteger('loading_cost')->nullable();        // هزینه بارگیری
                $table->unsignedBigInteger('return_amount')->nullable();       // برگشتی (ریال)
                $table->unsignedBigInteger('wagon_cost')->nullable();          // هزینه واگن (اگر is_wagon=true)

                // اطلاعات ویژه واگن
                $table->string('wagon_coordinator_mobile')->nullable(); // همراه هماهنگ‌کننده واگن
                $table->string('wagon_contact_phone')->nullable();      // شماره تماس واگن / کوپه

                $table->text('description')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_vehicles');
    }
};
