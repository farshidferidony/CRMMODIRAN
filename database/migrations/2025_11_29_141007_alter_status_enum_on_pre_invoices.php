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
                'ready_for_sales_pricing'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
