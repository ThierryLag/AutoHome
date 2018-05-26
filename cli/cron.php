<?php
error_reporting(E_ALL & ~E_NOTICE);
include_once __DIR__ . '/../vendor/autoload.php';

\Autohome\Timeline::load(__DIR__ . '/../config/app.yml')->start();
