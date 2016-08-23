<?php return [
    'sunrise' => [
        ['module' => 'niko_message', 'value' => 'Night mode : off'],
        ['module' => 'niko_switch', 'id' => 4, 'value' => 'off'],
    ],
    '06:20' => [
        ['module' => 'niko_message', 'value' => 'It\'s wake up time, dude.'],
        ['module' => 'niko_wakeup', 'id' => 6, 'value' => 100, 'speed' => 10],
    ],
    'sunset' => [
        ['module' => 'niko_switch', 'id' => 4, 'value' => 'on'],
    ],
    '23:30' => [
        ['module' => 'niko_message', 'value' => 'Night mode : on'],
        ['module' => 'niko_switch', 'id' => 4, 'value' => 'off'],
    ],
];
