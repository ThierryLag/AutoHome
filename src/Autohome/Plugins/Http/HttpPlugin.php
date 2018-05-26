<?php
/**
 * Extremely simple HTTP call
 */
namespace Autohome\Plugins\Http;

use Autohome\Plugins\PluginInterface;
use GuzzleHttp\Client as Guzzle;

class HttpPlugin implements PluginInterface
{
    protected $guzzle;

    public function __construct($options = [])
    {
        $this->guzzle = $options['guzzle'] ?: new Guzzle;
    }

    /**
     * @param array $action
     * @return boolean
     */
    public function execute($action)
    {
        if($action['address']) {
            $this->guzzle->get($action['address'], []);
        }
        return null;
    }
}