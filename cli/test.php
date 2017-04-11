<?php
error_reporting(E_ALL & ~E_NOTICE);
include_once __DIR__ . '/../vendor/autoload.php';

use \Autohome\Timeline;
$timeline = Timeline::load(__DIR__ . '/../config/app.yml');

for ($hour=0; $hour < 24; $hour++) {
    for ($minute=0; $minute < 60; $minute++) {
        $fakeNow = (new \DateTime())->setTime($hour,  $minute);
        $timeline->test($fakeNow->format('H:i'));
    }
}
