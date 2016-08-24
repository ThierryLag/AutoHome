<?php
namespace Autohome\Plugins\Slack;
use Autohome\Plugins\PluginInterface;
use Maknz\Slack\Client as SlackClient;

/**
 * Class MessagePlugin
 *
 * @package Autohome\Plugins\Slack
 *
 * @property SlackClient $slack
 */
class MessagePlugin implements PluginInterface
{
    protected $slack;

    public function __construct($options = [])
    {
        $this->slack = new SlackClient(
            $options['slack']['hook'],
            $options['slack']['options']
        );
    }

    public function execute($action)
    {
        $this->slack->send($action['message']);
        return true;
    }
}
