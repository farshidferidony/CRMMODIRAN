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
        // php artisan make:migration add_sales_fields_to_transports

        Schema::table('transports', function (Blueprint $table) {
            // بخش اول: تنظیمات حمل
            $table->boolean('unloading_confirmed')->nullable()->after('status');
            $table->enum('shipping_type', ['inner_city', 'outer_city'])->nullable();
            $table->enum('transfer_type', ['single_stage', 'two_stage'])->nullable();
            $table->enum('requested_truck_type', [
                'lowboy','flat_trailer','roll_trailer','side_trailer',
                'ten_wheeler','single','truck_911','khaawar','khaawar_steel',
                'nissan','nissan_steel','pickup','bunker'
            ])->nullable();
            $table->enum('requested_wagon_type', ['normal', 'russian'])->nullable();

            // بخش دوم: فرستنده
            $table->string('sender_name')->nullable();
            $table->string('sender_postal_code', 20)->nullable();
            $table->string('sender_national_code', 20)->nullable();
            $table->string('sender_phone', 30)->nullable();

            // بخش سوم: گیرنده و محل تخلیه
            $table->string('receiver_company')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_postal_code', 20)->nullable();
            $table->string('receiver_national_code', 20)->nullable();
            $table->string('receiver_phone', 30)->nullable();
            $table->string('receiver_mobile', 30)->nullable();
            $table->text('receiver_activity_address')->nullable();
            $table->boolean('unloading_place_approved')->nullable();
            $table->text('unloading_address')->nullable();
            $table->string('unloading_postal_code', 20)->nullable();
            $table->string('unloading_responsible')->nullable();
            $table->string('unloading_responsible_phone', 30)->nullable();

            $table->boolean('approved_by_sales_expert')->default(false);
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
