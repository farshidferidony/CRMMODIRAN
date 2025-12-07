<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pre_invoices', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE pre_invoices
                MODIFY COLUMN status ENUM(
                    'draft',
                    'waiting_purchase',
                    'approved_manager',
                    'priced_by_purchase',
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
        });

    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE pre_invoices 
            MODIFY COLUMN status ENUM(
                'draft',
                'waiting_purchase',
                'approved_manager',
                'confirmed',
                'rejected',
                'closed',
                'priced_by_purchase',
                'priced_by_sales',
                'waiting_sales_approval',
                'approved_by_sales_manager',
                'rejected_by_sales_manager',
                'waiting_finance_purchase',
                'finance_purchase_approved',
                'finance_purchase_rejected',
                'customer_approved',
                'customer_rejected',
                'advance_waiting_finance',
                'advance_finance_approved',
                'advance_finance_rejected'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
