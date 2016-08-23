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
     */
    public static function execute($options=[]);
}
