<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `pre_invoices`
            MODIFY COLUMN `status` ENUM(
                'draft',
                'waiting_purchase',
                'priced_by_purchase',
                'approved_manager',

                'priced_by_sales',
                'waiting_sales_approval',
                'approved_by_sales_manager',
                'rejected_by_sales_manager',

                'waiting_finance_purchase',
                'finance_purchase_approved',
                'finance_purchase_rejected',

                'confirmed',
                'customer_approved',
                'customer_rejected',

                'advance_waiting_finance',
                'advance_finance_approved',
                'advance_finance_rejected',

                'waiting_purchase_execution',
                'purchasing',
                'purchase_completed',

                'post_purchase_sales_approved',
                'shipping_requested',
                'shipping_prepared',
                'shipping_in_progress',
                'delivered',

                'invoiced',
                'closed',
                'rejected'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // بازگشت به نسخه قبلی بدون دو وضعیت جدید
        DB::statement("
            ALTER TABLE `pre_invoices`
            MODIFY COLUMN `status` ENUM(
                'draft',
                'waiting_purchase',
                'priced_by_purchase',
                'approved_manager',

                'priced_by_sales',
                'waiting_sales_approval',
                'approved_by_sales_manager',
                'rejected_by_sales_manager',

                'waiting_finance_purchase',
                'finance_purchase_approved',
                'finance_purchase_rejected',

                'confirmed',
                'customer_approved',
                'customer_rejected',

                'advance_waiting_finance',
                'advance_finance_approved',
                'advance_finance_rejected',

                'waiting_purchase_execution',
                'purchasing',
                'purchase_completed',

                'shipping_prepared',
                'shipping_in_progress',
                'delivered',

                'invoiced',
                'closed',
                'rejected'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};

