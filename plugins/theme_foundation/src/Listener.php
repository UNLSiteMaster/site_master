<?php

namespace SiteMaster\Plugins\Theme_Foundation;

use SiteMaster\Events\RoutesCompile;
use SiteMaster\Plugin\PluginListener;
use SiteMaster\Events\RegisterTheme;

class Listener extends PluginListener
{
    public function onRegisterTheme(RegisterTheme $event)
    {
        if ($event->getTheme() == 'foundation') {
            $event->setPlugin($this->plugin);
        }
    }
}