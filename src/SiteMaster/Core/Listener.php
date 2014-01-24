<?php
namespace SiteMaster\Core;

use SiteMaster\Core\Events\Navigation\MainCompile;
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

    public function onNavigationMainCompile(MainCompile $event)
    {
        $event->addNavigationItem(Config::get('URL') . 'registry/', 'Registry');
        
        if ($user = User\Session::getCurrentUser()) {
            $event->addNavigationItem($user->getURL(), 'My Sites');
        }
        
        if ($user && $user->isAdmin()) {
            $event->addNavigationItem(Config::get('URL') . 'admin/', 'Administration');
        }
    }
}