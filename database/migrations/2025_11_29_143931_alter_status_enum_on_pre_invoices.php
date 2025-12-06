<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
                'waiting_sales_approval'
            ) NOT NULL DEFAULT 'draft'
        ");
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
                'closed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
