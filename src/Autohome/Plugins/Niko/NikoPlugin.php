<?php
namespace Autohome\Plugins\Niko;
use Autohome\Plugins\PluginInterface;

abstract class NikoPlugin implements PluginInterface
{
    public static function execute($options=[])
    {
        return true;
    }
}
