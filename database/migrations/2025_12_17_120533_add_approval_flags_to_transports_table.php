<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            // این‌ها را قبلاً داری، پس دوباره اضافه نکن
            // $table->boolean('approved_by_transport_expert')->default(false);
            // $table->boolean('approved_by_purchase')->default(false);
            // $table->boolean('approved_by_sales_expert')->default(false);

            if (!Schema::hasColumn('transports', 'approved_by_accounting')) {
                $table->boolean('approved_by_accounting')->default(false)->after('approved_by_transport_expert');
            }

            if (!Schema::hasColumn('transports', 'approved_by_sales_manager')) {
                $table->boolean('approved_by_sales_manager')->default(false)->after('approved_by_accounting');
            }

            if (!Schema::hasColumn('transports', 'closed_by_logistics')) {
                $table->boolean('closed_by_logistics')->default(false)->after('approved_by_sales_manager');
            }

            if (!Schema::hasColumn('transports', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('closed_by_logistics');
            }
        });

        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn([
                'product_ids',
                'driver_id',
                'carrier_company_id',    // اگر الان با relation جدید استفاده نمی‌شود
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
                'expenses',
            ]);
        });

    }

    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn([
                'approved_by_accounting',
                'approved_by_sales_manager',
                'closed_by_logistics',
                'closed_at',
            ]);
        });
    }
};
