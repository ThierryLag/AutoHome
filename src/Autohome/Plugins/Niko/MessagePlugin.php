<?php
namespace Autohome\Plugins\Niko;

class MessagePlugin extends NikoPlugin
{
    public function execute($action)
    {
        echo "Message : " . $action['value'] . PHP_EOL;
        //$response = $this->sendCommand('sendmsg', ['value1' => (int) $value]);
        //return ($response && $response['error'] == 0) ? $value : false;

        return true;
    }
}
