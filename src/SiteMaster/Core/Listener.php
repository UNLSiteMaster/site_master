<?php
namespace SiteMaster\Core;

use SiteMaster\Core\Events\Navigation\MainCompile;
use SiteMaster\Core\Events\Navigation\SubCompile;
use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Events\Theme\RegisterScripts;
use SiteMaster\Core\Events\Theme\RegisterStyleSheets;
use SiteMaster\Core\Plugin\PluginListener;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^$/',                                             'SiteMaster\Core\Home\Home');
        $event->addRoute('/^registry\/$/',                                   'SiteMaster\Core\Registry\Search');
        $event->addRoute('/^logout\/$/',                                     'SiteMaster\Core\User\Logout');
        $event->addRoute('/^users\/(?P<provider>(.*))\/(?P<uid>(.*))\/$/',   'SiteMaster\Core\User\View');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/join\/$/',            'SiteMaster\Core\registry\site\JoinSiteForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/members\/$/',         'SiteMaster\Core\registry\site\MembersForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/members\/add\/$/',    'SiteMaster\Core\registry\site\AddMemberForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/verify\/$/',          'SiteMaster\Core\registry\site\VerifyForm');
        $event->addRoute('/^sites\/add\/$/',                                 'SiteMaster\Core\Registry\Site\AddSiteForm');
    }

    /**
     * Compile primary navigation
     * 
     * @param MainCompile $event
     */
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

    /**
     * Compile sub navigation
     * 
     * @param SubCompile $event
     */
    public function onNavigationSubCompile(SubCompile $event)
    {
        if ($user = User\Session::getCurrentUser()) {
            if ($event->isFor($user->getURL())) {
                $event->addNavigationItem(Config::get('URL') . 'sites/add/', 'Add a site');
            }
        }
        
        switch ($event->getFor()) {
            case Config::get('URL') . 'registry/':
                $event->addNavigationItem(Config::get('URL') . 'registry/all', 'All Sites');
                break;
        }
    }

    /**
     * @param RegisterStyleSheets $event
     */
    public function onThemeRegisterStyleSheets(RegisterStyleSheets $event)
    {
        $event->addStyleSheet(Config::get('URL') . 'www/css/core.css');
    }

    /**
     * @param RegisterScripts $event
     */
    public function onThemeRegisterScripts(RegisterScripts $event)
    {
        $event->addScript(Config::get('URL') . 'www/js/core.js');
    }
    
    public function onUserSearch(Events\User\Search $event)
    {
        $search = new User\Search(array('term' => $event->getSearchTerm()));
        
        foreach ($search as $result) {
            $event->addResult($result->provider, $result->uid, $result->email, $result->first_name, $result->last_name);
        }
    }
}