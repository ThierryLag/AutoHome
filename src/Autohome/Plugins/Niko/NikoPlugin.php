<?php
namespace Autohome\Plugins\Niko;
use Autohome\Plugins\PluginInterface;
use Autohome\Plugins\Niko\Exceptions\NikoPluginException;

/**
 * Class NikoPlugin
 *
 * @package Autohome\Plugins\Niko
 *
 * @property resource $socket
 */
abstract class NikoPlugin implements PluginInterface
{
    protected $socket;

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * @param array $options
     */
    public function __construct($options=[])
    {
        $this->init($options);
    }

    /**
     * @param array $options
     * @return $this
     * @throws NikoPluginException
     */
    public function init($options=[])
    {
        if (false === ($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            throw new NikoPluginException("Unable to create the socket", 1);
        }

        if (!isset($options['address']) || !$options['address']) {
            throw new NikoPluginException("No address specified.", 1);
        }

        if (strpos($options['address'], ':')) {
            list($address, $port) = explode(':', $options['address']);
        }
        else {
            $address = $options['address'];
            $port = isset($options['port']) ? $options['port'] : 8000;
        }

        if (false === socket_connect($this->socket, $address, $port)) {
            throw new NikoPluginException("Unable to connect the socket", 1);
        }

        return $this;
    }

    public function execute($action)
    {
        return true;
    }

    // ================================================================================================================

    /**
     * Send message to NHC
     *
     * @param $message
     *
     * @return string
     * @throws NikoPluginException
     */
    protected function send($message)
    {
        if (false === socket_write($this->socket, $message, strlen($message))) {
            throw new NikoPluginException("Error while sending the command", 1);
        }

        $response = '';
        do {
            $bytes = socket_recv($this->socket, $out, 1024, 0);
            $response .= trim($out);
        }
        while ($bytes == 0 || $bytes == 256);

        return $response;
    }

    /**
     * Send command with options to NHC and decode the response
     *
     * @param String $command
     * @param array $options
     *
     * @return mixed
     * @throws NikoPluginException
     */
    protected function sendCommand($command, $options=[])
    {
        $command = json_encode(array_merge(['cmd' => $command], $options));
        $datas = json_decode($this->send($command), true);
        return $datas['data'];
    }
}
