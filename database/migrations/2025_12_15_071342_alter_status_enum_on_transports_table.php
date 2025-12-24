<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `transports`
            MODIFY COLUMN `status` ENUM(
                'requested_by_sales',
                'completed_by_sales',
                'completed_by_purchase',
                'assigned_to_logistics',
                'in_progress',
                'vehicle_loaded',
                'shipped',
                'delivered',
                'checked_by_accounting',
                'checked_by_sales_manager',
                'closed',
                'logistics_completed',
                'accounting_approved',
                'sales_manager_approved'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `transports`
            MODIFY COLUMN `status` ENUM(
                'requested_by_sales',
                'completed_by_sales',
                'completed_by_purchase',
                'assigned_to_logistics',
                'in_progress',
                'vehicle_loaded',
                'shipped',
                'delivered',
                'checked_by_accounting',
                'checked_by_sales_manager',
                'closed'
            ) NOT NULL
        ");
    }
};