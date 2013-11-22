<?php

namespace SiteMaster\Plugins\Example;

use SiteMaster\Events\RoutesCompile;
use SiteMaster\Plugin\PluginListener;
use SiteMaster\Events\RegisterTheme;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^example\/$/', __NAMESPACE__ . '\Example');
    }

    public function onRegisterTheme(RegisterTheme $event)
    {
        if ($event->getTheme() == 'example') {
            $event->setPlugin($this->plugin);
        }
    }
}