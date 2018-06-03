# PHP cli for Home Automation (with NHC)

> **Warning WIP !** Use it at you own risk.

This PHP script must be call by cron job every minutes.
You can define actions in `config/timeline.yml` and, 
if the time match, the action will be execute.

You can implement various plugin for these actions, 
but my goal (for this project) is to use it with Smart Home and IoT, particularly for Niko Home Control. 

## Requirements

* PHP 7.0+

## Installation

* Clone the project on your server.
* Create configuration files `config/config.yml` and `config/timeline.yml` 
    You can copy and rename the sample files `.sample`
* Add an entry to your crontab to start the automation: `crontab -e`

        * * * * * php /path_to_project/cli/cron.php >> "/path_to_project/log/$(date +\%Y-\%m-\%d).log" 2>&1

* Restart cron service

## Usage

Edit the `config/timeline.yml` to definie the sequence you want.

### Regular Hours

Use the syntax _hh:mm_ to define precise hour : `06:20`, `18:40`

### Special Hours

The system also recognize these special words as specific times :

* `midnight` : at 00:00
* `sunrise` : at your location sunrise
* `noon` : near 12:00 according your location
* `sunset` : at your location sunset 
* `always` : every minutes (be carefull with this one)

_Your location must be define in `config/app.yml`._
