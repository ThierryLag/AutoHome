<?php
namespace Autohome\Plugins\Niko;

class MessagePlugin extends NikoPlugin
{
    public static function execute($options=[])
    {
        echo "Message : " . $options['value'] . PHP_EOL;
        return true;
    }
}
