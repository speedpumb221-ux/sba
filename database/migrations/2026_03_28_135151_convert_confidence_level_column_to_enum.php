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
        // Convert integer confidence_level to enum-like values
        $driver = \Illuminate\Support\Facades\DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // PostgreSQL: change to varchar and add a CHECK constraint limiting allowed values
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE speed_bumps ALTER COLUMN confidence_level TYPE varchar USING confidence_level::varchar");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE speed_bumps ALTER COLUMN confidence_level SET DEFAULT 'medium'");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE speed_bumps ALTER COLUMN confidence_level SET NOT NULL");
            // Add constraint if not exists
            \Illuminate\Support\Facades\DB::statement(<<<'SQL'
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_constraint WHERE conname = 'confidence_level_check'
                    ) THEN
                        ALTER TABLE speed_bumps ADD CONSTRAINT confidence_level_check CHECK (confidence_level IN ('low','medium','high'));
                    END IF;
                END$$;
            SQL
            );
        } else {
            // MySQL / MariaDB: use native ENUM type
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `speed_bumps` MODIFY `confidence_level` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // Remove check constraint if exists, then convert back to integer
            \Illuminate\Support\Facades\DB::statement(<<<'SQL'
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint WHERE conname = 'confidence_level_check'
                    ) THEN
                        ALTER TABLE speed_bumps DROP CONSTRAINT IF EXISTS confidence_level_check;
                    END IF;
                END$$;
            SQL
            );
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE speed_bumps ALTER COLUMN confidence_level TYPE integer USING confidence_level::integer");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE speed_bumps ALTER COLUMN confidence_level SET DEFAULT 50");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE speed_bumps ALTER COLUMN confidence_level SET NOT NULL");
        } else {
            // MySQL: change back to INT
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `speed_bumps` MODIFY `confidence_level` INT(11) NOT NULL DEFAULT 50");
        }
    }
};
