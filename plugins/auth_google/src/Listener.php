<?php
namespace SiteMaster\Plugins\Auth_Google;

use SiteMaster\Core\Events\GetAuthenticationPlugins;
use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Plugin\PluginListener;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^auth\/google\/$/', __NAMESPACE__ . '\Auth');
        $event->addRoute('/^auth\/google\/callback$/', __NAMESPACE__ . '\Auth');
    }
    
    public function onGetAuthenticationPlugins(GetAuthenticationPlugins $event)
    {
        $event->addPlugin($this->plugin);
    }
}