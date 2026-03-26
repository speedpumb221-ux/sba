<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillConfidenceLevel extends Command
{
    protected $signature = 'speedbumps:backfill-confidence';
    protected $description = 'Backfill confidence_level from numeric confidence for existing speed_bumps';

    public function handle()
    {
        $this->info('Backfilling confidence_level from numeric confidence...');

        $updated = DB::table('speed_bumps')
            ->whereNotNull('confidence')
            ->update(['confidence_level' => DB::raw("CASE WHEN confidence >= 80 THEN 'high' WHEN confidence >= 60 THEN 'medium' ELSE 'low' END")]);

        $this->info("Updated rows: {$updated}");
        return 0;
    }
}
