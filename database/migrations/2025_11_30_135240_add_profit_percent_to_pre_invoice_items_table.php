<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->decimal('profit_percent', 8, 2)->nullable()->after('purchase_unit_price');
        });
    }

    public function down()
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->dropColumn('profit_percent');
        });
    }

};
