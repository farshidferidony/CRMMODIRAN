<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchasePreInvoiceIdToPreInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->foreignId('purchase_pre_invoice_id')
                  ->nullable()
                  ->after('pre_invoice_id')
                  ->constrained('pre_invoices')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_pre_invoice_id']);
            $table->dropColumn('purchase_pre_invoice_id');
        });
    }
}
