<?php
namespace Autohome\Plugins\Niko;
use Autohome\Plugins\Niko\Exceptions\NikoPluginException;

class SwitchPlugin extends NikoPlugin
{
    public function execute($action)
    {
        if(!isset($action['id'])) {
            throw new NikoPluginException('Action switch must have an ID');
        }

        $id = $action['id'];
        $value = isset($action['value']) && (boolean) $action['value'] ? 100 : 0;

        $response = $this->sendCommand('executeactions', ['id' => $id, 'value1' => $value]);
        return ($response && $response['error'] == 0) ? $value : false;
    }
}
