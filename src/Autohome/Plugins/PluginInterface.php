<?php
namespace Autohome\Plugins;

/**
 * Define the comportment of Plugin used by Timeline controller.
 * Each plugin must implement this interface.
 */
interface PluginInterface
{
    public function __construct($options=[]);

    /**
     * Timeline plugin must implement execute method that will called by the Timeline controller.
     *
     * @param array $action
     * @return boolean
     */
    public function execute($action);
}
