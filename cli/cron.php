<?php
//error_reporting(E_ALL & ~E_NOTICE);
include_once __DIR__ . '/../vendor/autoload.php';

use \M1\Vars\Vars;
use \Autohome\Timeline;

$config = new Vars(__DIR__ . '/../config/app.yml', ['cache' => false]);
$timeline = new Vars(__DIR__ . '/../config/timeline.yml', ['cache' => false]);

//echo '<pre>', print_r($timeline->getContent(), true), '</pre>'; exit;

Timeline::load($config->getContent())
        ->start($timeline->getContent());

