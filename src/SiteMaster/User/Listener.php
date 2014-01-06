<?php
namespace SiteMaster\User;

use SiteMaster\Events\RoutesCompile;
use SiteMaster\Plugin\PluginListener;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^logout\/$/', __NAMESPACE__ . '\logout');
    }
}