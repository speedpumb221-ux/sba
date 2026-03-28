<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert numeric confidence_level values to enum strings
        $driver = \Illuminate\Support\Facades\DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // PostgreSQL: use integer cast and POSIX ~ operator for regexp
            \Illuminate\Support\Facades\DB::statement("\n                UPDATE speed_bumps\n                SET confidence_level = CASE\n                    WHEN CAST(confidence_level AS integer) >= 80 THEN 'high'\n                    WHEN CAST(confidence_level AS integer) >= 60 THEN 'medium'\n                    ELSE 'low'\n                END\n                WHERE confidence_level ~ '^[0-9]+$'\n            ");
        } else {
            // MySQL / MariaDB and others: use UNSIGNED cast and REGEXP
            \Illuminate\Support\Facades\DB::statement("\n                UPDATE speed_bumps\n                SET confidence_level = CASE\n                    WHEN CAST(confidence_level AS UNSIGNED) >= 80 THEN 'high'\n                    WHEN CAST(confidence_level AS UNSIGNED) >= 60 THEN 'medium'\n                    ELSE 'low'\n                END\n                WHERE confidence_level REGEXP '^[0-9]+$'\n            ");
        }
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
