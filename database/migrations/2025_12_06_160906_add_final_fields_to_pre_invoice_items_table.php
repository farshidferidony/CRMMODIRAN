<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->decimal('final_quantity', 15, 3)->nullable()->after('quantity');
            $table->decimal('final_unit_price', 15, 2)->nullable()->after('unit_price');
            $table->decimal('final_total_price', 15, 2)->nullable()->after('final_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('pre_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['final_quantity', 'final_unit_price', 'final_total_price']);
        });
    }
};