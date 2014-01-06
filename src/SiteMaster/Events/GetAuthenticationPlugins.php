<?php
namespace SiteMaster\Events;

use SiteMaster\Plugin\AuthenticationInterface;
use Symfony\Component\EventDispatcher\Event;

class GetAuthenticationPlugins extends Event
{
    const EVENT_NAME = 'plugins.getAuthentication';

    protected $plugins = array();

    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @param AuthenticationInterface $plugin
     */
    public function addPlugin(AuthenticationInterface $plugin)
    {
        $this->plugins[] = $plugin;
    }
}