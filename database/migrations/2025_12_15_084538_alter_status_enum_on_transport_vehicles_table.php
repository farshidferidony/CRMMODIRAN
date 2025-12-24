<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `transport_vehicles`
            MODIFY COLUMN `status` ENUM(
                'searching',
                'found',
                'loading',
                'loaded',
                'en_route',
                'arrived',
                'unloading',
                'unloaded'
            ) NOT NULL DEFAULT 'searching'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `transport_vehicles`
            MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'searching'
        ");
    }
};
