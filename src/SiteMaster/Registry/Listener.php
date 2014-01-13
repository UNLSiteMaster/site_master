<?php
namespace SiteMaster\Registry;

use SiteMaster\Events\RoutesCompile;
use SiteMaster\Plugin\PluginListener;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^registry\/$/', __NAMESPACE__ . '\Search');
    }
}