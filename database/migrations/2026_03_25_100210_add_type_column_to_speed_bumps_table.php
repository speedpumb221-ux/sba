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
        Schema::table('speed_bumps', function (Blueprint $table) {
            $table->enum('type', ['normal', 'speed_bump', 'hump', 'bump', 'rumble_strip'])->default('normal')->after('description');
        });
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
