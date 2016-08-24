<?php
namespace Autohome\Plugins\Niko;

class MessagePlugin extends NikoPlugin
{
    public function execute($action)
    {
        echo "Message : " . $action['value'] . PHP_EOL;
        return true;
    }
}
