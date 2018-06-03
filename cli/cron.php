<?php
/**
 * Add the line below to crontab : crontab -e
 *      * * * * * php {{PATH}}autohome/cli/cron.php >> "{{PATH}}autohome/log/$(date +\%Y-\%m-\%d).log" 2>&1
 */

error_reporting(E_ALL & ~E_NOTICE);
include_once __DIR__ . '/../vendor/autoload.php';

\Autohome\Timeline::load(__DIR__ . '/../config/app.yml')->start();
