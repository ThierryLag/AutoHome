<?php
namespace Autohome\Plugins\Niko;

class SwitchPlugin extends NikoPlugin
{
    protected $values = [
        'on' => 1, 'open' => 1, '254' => 1,
        'off' => 0, 'close' => 0, '255' => 0,
    ];
}
