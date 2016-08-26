<?php
namespace Autohome\Plugins\Niko;

class SwitchPlugin extends NikoPlugin
{
    public function execute($action)
    {
        // TODO : Add exception implementation
        isset($action['id']) or die('Action switch must have an ID');
        $this->connectCall(sprintf('action/%d/%d', $action['id'], $action['value'] ?: 0));

        return true;
    }
}
