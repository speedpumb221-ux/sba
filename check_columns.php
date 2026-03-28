<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('speed_bumps');
foreach($columns as $col) {
    $type = Schema::getColumnType('speed_bumps', $col);
    echo $col . ': ' . $type . PHP_EOL;
}