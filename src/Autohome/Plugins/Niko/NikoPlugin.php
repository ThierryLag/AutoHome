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
    protected $values = [];

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * @param array $options
     * @throws NikoPluginException
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
        $id = $this->id($action);
        $value = $this->value($action);

        $response = $this->sendCommand('executeactions', ['id' => $id, 'value1' => $value]);
        return ($response && $response['error'] == 0) ? $value : false;
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

    protected function id($value)
    {
        if (is_array($value)) {
            if(!isset($value['id'])) {
                throw new NikoPluginException('Action must have an ID.');
            }
            $value = $value['id'];
        }

        return $value;
    }

    protected function value($value)
    {
        if (is_array($value)) {
            if(!isset($value['value'])) {
                throw new NikoPluginException('Action switch must have a value');
            }
            $value = $value['value'];
        }

        $value = strtolower($value);

        return isset($this->values[$value])
            ? $this->values[$value] : $value;
    }
}
