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
        // Convert integer confidence_level to enum
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `speed_bumps` MODIFY `confidence_level` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to integer
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `speed_bumps` MODIFY `confidence_level` INT(11) NOT NULL DEFAULT 50");
    }
};
