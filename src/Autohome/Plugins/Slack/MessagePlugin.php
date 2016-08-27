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
            $options['hook'],
            $options['options']
        );
    }

    public function execute($action)
    {
        if($action['message']) {
            $message = $this->slack->createMessage();
            isset($action['to']) && $message->to($action['to']);
            isset($action['icon']) && $message->setIcon($action['icon']);

            $message->send($action['message']);
        }
        return true;
    }
}
