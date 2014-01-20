<?php

namespace SiteMaster\Plugins\Example;

use SiteMaster\Core\Events\Navigation\MainCompile;
use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Plugin\PluginListener;
use SiteMaster\Core\Events\RegisterTheme;

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

    public function onNavigationMainCompile(MainCompile $event)
    {
        $event->addNavigationItem(\SiteMaster\Core\Config::get('URL') . 'example/', 'Example');
    }
}