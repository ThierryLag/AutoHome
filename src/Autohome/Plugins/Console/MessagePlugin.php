<?php
namespace Autohome\Plugins\Console;

use Autohome\Plugins\PluginInterface;

class MessagePlugin implements PluginInterface
{
    /**
     * @param array $options
     */
    public function __construct($options=[])
    {
    }

    public function execute($action)
    {
        if (isset($action['range'])) {
            $action['calculated_value'] = round($action['start'] + (100 - $action['start'] - $action['end']) * $action['precent']);
        }

        echo "Message : ", preg_replace_callback('/\{.*\}/', function($matches) use ($action) {
            $keys = substr($matches[0], 1, -1);
            return isset($action[$keys]) ? $action[$keys] : '-';
        }, $action['message']), PHP_EOL;

        return true;
    }
}
