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
        if (Schema::hasColumn('speed_bumps', 'type')) {
            // If the string column exists from early schema, change it into a proper enum.
            // This avoids "Column already exists" when running migrate:fresh on current code.
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `speed_bumps` MODIFY `type` ENUM('normal', 'speed_bump', 'hump', 'bump', 'rumble_strip') NOT NULL DEFAULT 'normal'");
        } else {
            Schema::table('speed_bumps', function (Blueprint $table) {
                $table->enum('type', ['normal', 'speed_bump', 'hump', 'bump', 'rumble_strip'])->default('normal')->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speed_bumps', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
