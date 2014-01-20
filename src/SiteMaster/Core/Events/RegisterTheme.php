<?php
namespace SiteMaster\Core\Events;

use SiteMaster\Core\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\Event;

class RegisterTheme extends Event
{
    const EVENT_NAME = 'themes.register';

    protected $plugin = false;
    protected $theme  = false;

    /**
     * @param $theme
     */
    public function __construct($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return bool|string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return bool|PluginInterface
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param PluginInterface $plugin
     */
    public function setPlugin(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }
}