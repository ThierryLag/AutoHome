<?php
namespace Autohome\Plugins\Niko;

class MessagePlugin extends NikoPlugin
{
    public static function execute($action, $options=[])
    {
        $instance = self::load();

        echo "Message : " . $action['value'] . PHP_EOL;

        $instance->connectClose();
        return true;
    }
}
