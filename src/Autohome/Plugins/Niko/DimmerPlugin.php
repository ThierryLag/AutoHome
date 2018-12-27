<?php
namespace Autohome\Plugins\Niko;

class DimmerPlugin extends NikoPlugin
{
    /**
     * Possible values for Dimmer :
     *  0-100 : set the value in percent
     *  254 : recall the last value (Warning if defined to 0).
     *  255 : shutdown without overwrite the value
     */
    protected $values = [
        'on' => 254,
        'off' => 255,
    ];
}
