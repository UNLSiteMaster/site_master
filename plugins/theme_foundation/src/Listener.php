<?php

namespace SiteMaster\Plugins\Theme_Foundation;

use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Plugin\PluginListener;
use SiteMaster\Core\Events\RegisterTheme;

class Listener extends PluginListener
{
    public function onRegisterTheme(RegisterTheme $event)
    {
        if ($event->getTheme() == 'foundation') {
            $event->setPlugin($this->plugin);
        }
    }
}