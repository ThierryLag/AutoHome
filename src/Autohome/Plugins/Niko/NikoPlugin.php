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
    protected $connect;
    protected $nikoweb;

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * @param array $options
     * @return NikoPlugin
     */
    public function __construct($options=[])
    {
        $this->init($options);
    }

    /**
     * @param array $options
     * @return $this
     */
    public function init($options=[])
    {
        $this->nikoweb = $options['url'] ?: 'http://0.0.0.0';
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
        echo $url, PHP_EOL;

        curl_setopt($this->connect, CURLOPT_URL, $url);
        return curl_exec($this->connect);
    }

    protected function connectClose()
    {
        curl_close($this->connect);
    }
}
