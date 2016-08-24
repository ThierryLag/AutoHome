<?php
namespace Autohome\Plugins;

/**
 * Define the comportment of Plugin used by Timeline controller.
 * Each plugin must implement this interface.
 */
interface PluginInterface
{
    /**
     * Timeline plugin must implement execute method that will called by the Timeline controller.
     *
     * @param array $action
     * @param array $options
     * @return boolean
     */
    public static function execute($action, $options = []);
}
