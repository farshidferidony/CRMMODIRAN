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
        if (!Schema::hasTable('transport_vehicle_items')) {
            Schema::create('transport_vehicle_items', function (Blueprint $table) {
                $table->id();

                $table->foreignId('transport_vehicle_id')
                    ->constrained('transport_vehicles')
                    ->cascadeOnDelete();

                // ردیف محل بارگیری (همان loading از Step 2)
                $table->foreignId('transport_loading_id')
                    ->constrained('transport_loadings') // اسم جدول loadings تو هرچی هست همین‌جا جایگزین کن
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->decimal('quantity', 15, 3); // مقدار از آن محصول که این ماشین/واگن می‌برد

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
        Schema::dropIfExists('transport_vehicle_items');
    }
};
