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
            if (!Schema::hasColumn('speed_bumps', 'confidence_level')) {
                $table->enum('confidence_level', ['low', 'medium', 'high'])->default('medium')->after('confidence');
                $table->index('confidence_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speed_bumps', function (Blueprint $table) {
            if (Schema::hasColumn('speed_bumps', 'confidence_level')) {
                $table->dropIndex(['confidence_level']);
                $table->dropColumn('confidence_level');
            }
        });
    }
};
