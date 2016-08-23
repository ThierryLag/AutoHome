<?php return [
    'sunrise' => [
        ['plugin' => 'niko_message', 'value' => 'Night mode : off'],
        ['plugin' => 'niko_switch', 'id' => 4, 'value' => 'off'],
    ],
    '06:30' => [
        ['plugin' => 'niko_message', 'value' => 'It\'s wake up time, dude.'],
        ['plugin' => 'niko_wakeup', 'id' => 6, 'value' => 100, 'speed' => 10],
    ],
    'sunset' => [
        ['plugin' => 'niko_switch', 'id' => 4, 'value' => 'on'],
    ],
    '23:00' => [
        ['plugin' => 'niko_message', 'value' => 'Night mode : on'],
        ['plugin' => 'niko_switch', 'id' => 4, 'value' => 'off'],
    ],
];
