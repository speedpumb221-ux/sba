<?php
require_once 'bootstrap/app.php';

try {
    $bump = new App\Models\SpeedBump();
    $bump->latitude = 24.722327;
    $bump->longitude = 46.693481;
    $bump->description = 'test';
    $bump->confidence_level = 'medium';
    $bump->save();
    echo 'Success: ' . $bump->id . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}