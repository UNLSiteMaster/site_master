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
        $event->addRoute(
            '/^$/',
            'SiteMaster\Core\Home\Home'
        );
        $event->addRoute(
            '/^registry\/$/',
            'SiteMaster\Core\Registry\Search'
        );
        $event->addRoute(
            '/^logout\/$/',
            'SiteMaster\Core\User\Logout'
        );
        $event->addRoute(
            '/^metrics\/$/',
            'SiteMaster\Core\Auditor\Metrics\View'
        );
        $event->addRoute(
            '/^metrics\/(?P<metrics_id>(\d*))\/$/',
            'SiteMaster\Core\Auditor\Metric\View'
        );
        $event->addRoute(
            '/^metrics\/(?P<metrics_id>(\d*))\/marks\/(?P<marks_id>(\d*))\/$/',
            'SiteMaster\Core\Auditor\Metric\Mark\View'
        );
        $event->addRoute(
            '/^users\/(?P<provider>(.*))\/(?P<uid>(.*))\/$/',
            'SiteMaster\Core\User\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/$/',
            'SiteMaster\Core\Registry\Site\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/scan\/$/',
            'SiteMaster\Core\Auditor\Site\ScanForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/scan\/page\/$/',
            'SiteMaster\Core\Auditor\Site\Page\ScanForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/hot-spots\/(?P<scans_id>(\d*))\/(?P<metrics_id>(\d*))\/$/',
            'SiteMaster\Core\Auditor\Scan\HotSpots'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/pages\/(?P<pages_id>(\d*))\/$/',
            'SiteMaster\Core\Auditor\Site\Page\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/pages\/(?P<pages_id>(\d*))\/marks\/(?P<page_marks_id>(\d*))\/$/',
            'SiteMaster\Core\Auditor\Site\Page\Mark\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/pages\/(?P<pages_id>(\d*))\/links-to-this\/$/',
            'SiteMaster\Core\Auditor\Site\Page\ViewLinksToPage'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/reviews(\/(?P<reviews_id>(\d*)))?\/edit\/$/',
            'SiteMaster\Core\Auditor\Site\Review\EditForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/reviews\/$/',
            'SiteMaster\Core\Auditor\Site\Review\ViewAllForSite'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/reviews\/(?P<reviews_id>(\d*))?\/$/',
            'SiteMaster\Core\Auditor\Site\Review\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/scans\/(?P<scans_id>(\d*))\/$/',
            'SiteMaster\Core\Auditor\Scan\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/scans\/$/',
            'SiteMaster\Core\Auditor\Site\Scans\View'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/scans\/(?P<scans_id>(\d*))\/changes\/$/',
            'SiteMaster\Core\Auditor\Scan\Changes'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/scans\/(?P<scans_id>(\d*))\/progress\/$/',
            'SiteMaster\Core\Auditor\Scan\Progress'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/edit\/$/',
            'SiteMaster\Core\Registry\Site\EditForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/join\/?((?P<users_id>(\d*))\/)$/',
            'SiteMaster\Core\Registry\Site\JoinSiteForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/members\/$/',
            'SiteMaster\Core\Registry\Site\MembersForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/members\/add\/$/',
            'SiteMaster\Core\Registry\Site\AddMemberForm'
        );
        $event->addRoute(
            '/^sites\/(?P<site_id>(\d*))\/verify\/?((?P<users_id>(\d*))\/)$/',
            'SiteMaster\Core\Registry\Site\VerifyForm'
        );
        $event->addRoute(
            '/^sites\/add\/$/',
            'SiteMaster\Core\Registry\Site\AddSiteForm'
        );
        $event->addRoute(
            '/^admin\/sites\/$/',
            'SiteMaster\Core\Admin\AllSites'
        );
        $event->addRoute(
            '/^admin\/reviews\/$/',
            'SiteMaster\Core\Admin\Reviews'
        );
        $event->addRoute(
            '/^qa-test\/$/',
            'SiteMaster\Core\Auditor\QATestController'
        );
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

        $event->addNavigationItem(Config::get('URL') . 'metrics/', 'Metrics');
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

            if ($event->isFor(Config::get('URL') . 'admin/') && $user->isAdmin()) {
                $event->addNavigationItem(Config::get('URL') . 'admin/sites/', 'All Sites');
                $event->addNavigationItem(Config::get('URL') . 'admin/reviews/', 'Unfinished Manual Reviews');
            }
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

        $event->addNavigationItem($site->getURL() . 'scans/', 'Scan History');
        
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
                $members_title = 'Add/Edit Members';
            }

            $event->addNavigationItem($site->getURL() . 'members/', $members_title);

            if ($is_verified || $user->isAdmin()) {
                $event->addNavigationItem($site->getURL() . 'edit/', 'Edit Site Info');
                $event->addNavigationItem($site->getURL() . 'reviews/', 'Manual Reviews');
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
        $event->addStyleSheet(Config::get('URL') . 'www/css/vendor/tablesorter.default.css');
    }

    /**
     * @param RegisterScripts $event
     */
    public function onThemeRegisterScripts(RegisterScripts $event)
    {
        $event->addScript(Config::get('URL') . 'www/js/vendor/modernizr.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/jquery.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/jquery.flexnav.min.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/jquery.tablesorter.min.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/nanobar.min.js');
        $event->addScript(Config::get('URL') . 'www/js/vendor/jquery.scrolltofixed-min.js');
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