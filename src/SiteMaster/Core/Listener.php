<?php

namespace SiteMaster\Core;

use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Plugin\PluginListener;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^$/',           'SiteMaster\Core\Home\Home');
        $event->addRoute('/^registry\/$/', 'SiteMaster\Core\Registry\Search');
        $event->addRoute('/^logout\/$/',   'SiteMaster\Core\User\Logout');
    }
}