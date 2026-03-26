<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application (so Eloquent and Facades work)
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DeviceEvent;
use App\Services\RoadEventAnalyzerService;

// create two test users
$u1 = User::create([
    'name' => 'T1',
    'email' => 't1_' . time() . '@example.com',
    'password' => bcrypt('secret'),
]);

$u2 = User::create([
    'name' => 'T2',
    'email' => 't2_' . (time() + 1) . '@example.com',
    'password' => bcrypt('secret'),
]);

// create device events near the same location
DeviceEvent::create([
    'user_id' => $u1->id,
    'latitude' => 24.7136,
    'longitude' => 46.6753,
    'speed' => 50,
    'vibration_magnitude' => 13,
]);

DeviceEvent::create([
    'user_id' => $u1->id,
    'latitude' => 24.71361,
    'longitude' => 46.67531,
    'speed' => 35,
    'vibration_magnitude' => 5,
]);

DeviceEvent::create([
    'user_id' => $u2->id,
    'latitude' => 24.71359,
    'longitude' => 46.67529,
    'speed' => 40,
    'vibration_magnitude' => 14,
]);

$svc = new RoadEventAnalyzerService();
$res = $svc->analyzeLocation(24.7136, 46.6753, 20, 24);

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
