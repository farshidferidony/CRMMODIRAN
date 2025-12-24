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
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->enum('freight_accounting_status', ['pending', 'approved', 'rejected'])->default('pending')->after('total_freight_amount');

            $table->text('freight_reject_reason')->nullable()->after('freight_accounting_status');

            $table->timestamp('freight_approved_at')->nullable()->after('freight_reject_reason');
            $table->unsignedBigInteger('freight_approved_by')->nullable()->after('freight_approved_at');

            $table->timestamp('freight_paid_at')->nullable()->after('freight_approved_by');
            $table->unsignedBigInteger('freight_paid_by')->nullable()->after('freight_paid_at');

            $table->boolean('freight_settled')->default(false)->after('freight_paid_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'freight_accounting_status',
                'freight_reject_reason',
                'freight_approved_at',
                'freight_approved_by',
                'freight_paid_at',
                'freight_paid_by',
                'freight_settled',
            ]);
        });
    }
};
