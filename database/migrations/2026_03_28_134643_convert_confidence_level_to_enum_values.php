<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert numeric confidence_level values to enum strings
        \Illuminate\Support\Facades\DB::statement("
            UPDATE speed_bumps
            SET confidence_level = CASE
                WHEN CAST(confidence_level AS UNSIGNED) >= 80 THEN 'high'
                WHEN CAST(confidence_level AS UNSIGNED) >= 60 THEN 'medium'
                ELSE 'low'
            END
            WHERE confidence_level REGEXP '^[0-9]+$'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert enum strings back to numeric values
        \Illuminate\Support\Facades\DB::statement("
            UPDATE speed_bumps
            SET confidence_level = CASE
                WHEN confidence_level = 'high' THEN 85
                WHEN confidence_level = 'medium' THEN 65
                ELSE 35
            END
        ");
    }
};
