<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {


            $table->date('shipping_date')->nullable()->after('truck_type');
            $table->decimal('total_freight', 18, 2)->nullable()->after('shipping_date');
            $table->decimal('loading_cost', 18, 2)->nullable()->after('total_freight');
            $table->string('waybill_number')->nullable()->after('loading_cost');
            $table->decimal('return_cost', 18, 2)->nullable()->after('waybill_number');

            // پلاک
            $table->string('plate_iran', 4)->nullable()->after('return_cost');
            $table->string('plate_3digits', 3)->nullable()->after('plate_iran');
            $table->string('plate_literal', 2)->nullable()->after('plate_3digits');
            $table->string('plate_2digits', 2)->nullable()->after('plate_literal');

            $table->string('carrier_attachment_path')->nullable()->after('plate_2digits');
            $table->boolean('approved_by_transport_expert')->default(false)->after('carrier_attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn([
                // اگر قبلاً driver_id داشتی و استفاده می‌کنی، این را از لیست حذف کن
                'truck_type',
                'shipping_date',
                'total_freight',
                'loading_cost',
                'waybill_number',
                'return_cost',
                'plate_iran',
                'plate_3digits',
                'plate_literal',
                'plate_2digits',
                'carrier_attachment_path',
                'approved_by_transport_expert',
            ]);
        });
    }
};

