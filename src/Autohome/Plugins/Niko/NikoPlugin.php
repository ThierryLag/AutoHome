<?php
namespace Autohome\Plugins\Niko;
use Autohome\Plugins\PluginInterface;

/**
 * Class NikoPlugin
 *
 * @package Autohome\Plugins\Niko
 *
 * @property resource $connect
 * @property string $nikoweb
 */
abstract class NikoPlugin implements PluginInterface
{
    protected static $instance = null;
    protected $connect;
    protected $nikoweb;

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * @param array $options
     * @return NikoPlugin
     */
    public function __construct($options=[])
    {
        if(!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance->init($options);
    }

    /**
     * @param array $options
     * @return $this
     */
    public function init($options=[])
    {
        $this->nikoweb = $options['nikoweb'] ?: 'http://0.0.0.0';
        $this->connectInit();

        return $this;
    }

    public function execute($action)
    {
        return true;
    }

    // ================================================================================================================

    protected function connectInit()
    {
        if(!$this->connect) {
            $this->connect = curl_init();

            curl_setopt($this->connect, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->connect, CURLOPT_HEADER, false);
            curl_setopt($this->connect, CURLOPT_FOLLOWLOCATION, true);
        }

        return $this;
    }

    protected function connectCall($url='')
    {
        $url = sprintf('%s/%s', $this->nikoweb, $url);
        curl_setopt($this->connect, CURLOPT_URL, $url);
        return curl_exec($this->connect);
    }

    protected function connectClose()
    {
        curl_close($this->connect);
    }
}
