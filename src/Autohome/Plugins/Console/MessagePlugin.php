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
        echo "Message : ", preg_replace_callback('/\{.*\}/', function($matches) use ($action) {
            $keys = substr($matches[0], 1, -1);
            return isset($action[$keys]) ? $action[$keys] : '-';
        }, $action['message']), PHP_EOL;

        return true;
    }
}
