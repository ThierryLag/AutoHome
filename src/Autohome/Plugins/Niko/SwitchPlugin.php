<?php
namespace Autohome\Plugins\Niko;

class SwitchPlugin extends NikoPlugin
{
    public static function execute($action, $options=[])
    {
        // TODO : Add exception implementation
        isset($action['id']) or die('Action switch must have an ID');

        $instance = self::load($options);
        $instance->connectCall(
            sprintf('action/%d/%d', $action['id'], $action['value'] ?: 0)
        );
        $instance->connectClose();

        return true;
    }
}
