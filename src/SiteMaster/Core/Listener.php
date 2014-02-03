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
        $event->addRoute('/^$/',                                                            'SiteMaster\Core\Home\Home');
        $event->addRoute('/^registry\/$/',                                                  'SiteMaster\Core\Registry\Search');
        $event->addRoute('/^logout\/$/',                                                    'SiteMaster\Core\User\Logout');
        $event->addRoute('/^users\/(?P<provider>(.*))\/(?P<uid>(.*))\/$/',                  'SiteMaster\Core\User\View');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/$/',                                 'SiteMaster\Core\Registry\Site\View');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/edit\/$/',                           'SiteMaster\Core\Registry\Site\EditForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/join\/?((?P<users_id>(\d*))\/)$/',   'SiteMaster\Core\Registry\Site\JoinSiteForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/members\/$/',                        'SiteMaster\Core\Registry\Site\MembersForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/members\/add\/$/',                   'SiteMaster\Core\Registry\Site\AddMemberForm');
        $event->addRoute('/^sites\/(?P<site_id>(\d*))\/verify\/?((?P<users_id>(\d*))\/)$/', 'SiteMaster\Core\Registry\Site\VerifyForm');
        $event->addRoute('/^sites\/add\/$/',                                                'SiteMaster\Core\Registry\Site\AddSiteForm');
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
     * Compile sub navigation
     *
     * @param \SiteMaster\Core\Events\Navigation\SiteCompile|\SiteMaster\Core\Events\Navigation\SubCompile $event
     */
    public function onNavigationSiteCompile(Events\Navigation\SiteCompile $event)
    {
        $site = $event->getSite();
        
        $event->addNavigationItem($site->getURL(), 'Current Report');
        
        
        $user = User\Session::getCurrentUser();
        
        if ($user) {
            $is_verified   = $site->userIsVerified($user);
            $membership    = $site->getMembershipForUser($user);
            $join_title    = 'Join';
            $members_title = 'Members';
            
            if ($membership) {
                $join_title = 'Add/Edit My Roles';
            }

            $event->addNavigationItem($site->getURL() . 'join/', $join_title);
            
            if ($is_verified) {
                $members_title = 'Add\Edit Members';
            }

            $event->addNavigationItem($site->getURL() . 'members/', $members_title);

            if ($is_verified) {
                $event->addNavigationItem($site->getURL() . 'edit/', 'Edit');
            }
        } else {
            $event->addNavigationItem($site->getURL() . 'members/', 'Members');
        }
    }

    /**
     * @param RegisterStyleSheets $event
     */
    public function onThemeRegisterStyleSheets(RegisterStyleSheets $event)
    {
        $event->addStyleSheet(Config::get('URL') . 'www/css/core.css');
        
        $event->addStyleSheet(Config::get('URL') . 'www/css/vendor/flexnav.css');
    }

    /**
     * @param RegisterScripts $event
     */
    public function onThemeRegisterScripts(RegisterScripts $event)
    {
        $event->addScript(Config::get('URL') . 'www/js/vendor/modernizr.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/jquery.js');
        $event->addScript(Config::get('URL') . 'www/js/core.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/jquery.flexnav.min.js');
    }
    
    public function onUserSearch(Events\User\Search $event)
    {
        $search = new User\Search(array('term' => $event->getSearchTerm()));
        
        foreach ($search as $result) {
            $event->addResult($result->provider, $result->uid, $result->email, $result->first_name, $result->last_name);
        }
    }
}