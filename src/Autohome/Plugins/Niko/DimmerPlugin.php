<?php
namespace Autohome\Plugins\Niko;
use Autohome\Plugins\Niko\Exceptions\NikoPluginException;

class DimmerPlugin extends NikoPlugin
{
    public function execute($action)
    {
        if(!isset($action['id'])) {
            throw new NikoPluginException('Action dimmer must have an ID');
        }

        $id = $action['id'];
        $value = isset($action['value']) ? $action['value'] : 0;

        if(isset($action['range']) && $action['range']) {
            $value = round($action['start']
                   + (100 - $action['start'] - $action['end']) * $action['percent']);
        }

        /**
         * Value :
         *  0-100 : set the value
         *  254 : recall the last value
         *  255 : shutdown with overwrite the value
         */

        $response = $this->sendCommand('executeactions', ['id' => $id, 'value1' => $value]);
        return ($response && $response['error'] == 0) ? $value : false;
    }
}
