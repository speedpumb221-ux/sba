<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OSMSpeedBumpService;

class ImportOsmBumps extends Command
{
    protected $signature = 'osm:import-bumps {latitude?} {longitude?} {radius=1000}';
    protected $description = 'Import nearby speed bumps from OpenStreetMap (Overpass) and store them as source=osm';

    public function handle()
    {
        $lat = $this->argument('latitude') ?? 24.7136;
        $lng = $this->argument('longitude') ?? 46.6753;
        $radius = (int) $this->argument('radius');

        $this->info("Importing OSM speed bumps around {$lat},{$lng} radius {$radius}m...");

        $svc = new OSMSpeedBumpService();
        $res = $svc->fetchAndStoreNearby((float)$lat, (float)$lng, $radius);

        if (!empty($res['success'])) {
            $this->info('Imported: ' . ($res['count'] ?? 0) . ' bumps.');
            return 0;
        }

        $this->error('Import failed: ' . ($res['status'] ?? 'unknown'));
        return 1;
    }
}
