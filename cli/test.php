<?php
error_reporting(E_ALL & ~E_NOTICE);
include_once __DIR__ . '/../vendor/autoload.php';

use \Autohome\Timeline;
$timeline = Timeline::load(__DIR__ . '/../config/app.yml');

// ----------------------------------------------------------------------------

// Handles the parameters
if ($parameters = array_slice($argv, 1)) {
    $moments = Timeline::specialTimes();

    foreach ($parameters as $parameter) {
        if (isset($moments[$parameter])) {
            $currentTime = $moments[$parameter]->format('H:i');
        }
        elseif (preg_match('/^([0-2]?[0-9]{1}):([0-5]{1}[0-9]{1})/', $parameter, $matches)) {
            $currentTime = $matches[0];
        }
    }
}

// ----------------------------------------------------------------------------

// Current time
if ($currentTime) {
    echo "Test with the current time set to: ", $currentTime, PHP_EOL;
    $timeline->test($currentTime, !getopt('f'));

    exit();
}

// Get all times
echo "Simulate for all times: ", PHP_EOL;
for ($hour=0; $hour < 24; $hour++) {
    for ($minute=0; $minute < 60; $minute++) {
        $fakeNow = (new \DateTime())->setTime($hour,  $minute)->format('H:i');
        $timeline->test($fakeNow);
    }
}
