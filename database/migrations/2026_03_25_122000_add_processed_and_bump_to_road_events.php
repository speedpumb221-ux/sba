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
        Schema::table('road_events', function (Blueprint $table) {
            if (!Schema::hasColumn('road_events', 'is_processed')) {
                $table->boolean('is_processed')->default(false)->after('vibration');
            }

            if (!Schema::hasColumn('road_events', 'speed_bump_id')) {
                $table->foreignId('speed_bump_id')->nullable()->constrained('speed_bumps')->onDelete('set null')->after('is_processed');
                $table->index('speed_bump_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('road_events', function (Blueprint $table) {
            if (Schema::hasColumn('road_events', 'speed_bump_id')) {
                $table->dropForeign(['speed_bump_id']);
                $table->dropIndex(['speed_bump_id']);
                $table->dropColumn('speed_bump_id');
            }

            if (Schema::hasColumn('road_events', 'is_processed')) {
                $table->dropColumn('is_processed');
            }
        });
    }
};
