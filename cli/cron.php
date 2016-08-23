<?php
//error_reporting(E_ALL & ~E_NOTICE);
include_once __DIR__ . '/../vendor/autoload.php';

$config = require_once __DIR__ . '/../datas/config.php';
$timeline = require_once __DIR__ . '/../datas/timeline.php';

\Autohome\TimeLine::load($config)->start($timeline);

