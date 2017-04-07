<?php

namespace SiteMaster\Core;

use SiteMaster\Core\Events\Navigation\MainCompile;
use SiteMaster\Core\Events\Navigation\SubCompile;
use SiteMaster\Core\Events\Theme\RegisterScripts;
use SiteMaster\Core\Events\Theme\RegisterStyleSheets;
use SiteMaster\Core\Plugin\PluginInterface;
use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Registry\Site\Role;

/**
 * This class is the plugin class for the core system.
 * It will house the install/uninstall process for the core system and the routes/events
 * 
 * The theory here is that by putting all these in the same 'plugin', it will be easier to maintain core.
 * 
 * Class Plugin
 * @package SiteMaster\Core
 */
class Plugin extends PluginInterface
{

    /**
     * Called when a plugin is installed.  Add sql changes and other logic here.
     *
     * @return mixed
     */
    public function onInstall()
    {
        $sql = file_get_contents(Util::getRootDir() . "/data/database.sql");

        if (!Util::execMultiQuery($sql, true)) {
            return false;
        }
        
        //Set up the default roles
        if (!Role::getByRoleName('admin')) {
            Role::createRole('admin', array(
                'description' => 'administrative member of the site.  People with this role can add/edit other user\'s roles'
            ));
        }
        
        if (!Role::getByRoleName('developer')) {
            Role::createRole('developer', array(
                'description' => 'responsible for developing the site code'
            ));
        }

        if (!Role::getByRoleName('operator')) {
            Role::createRole('operator', array(
                'description' => 'responsible for general support'
            ));
        }

        if (!Role::getByRoleName('content')) {
            Role::createRole('content', array(
                'description' => 'responsible for content development on the site'
            ));
        }

        if (!Role::getByRoleName('sysadmin')) {
            Role::createRole('sysadmin', array(
                'description' => 'responsible for system hosting the site'
            ));
        }
        
        return true;
    }

    /**
     * Please undo whatever you did in onInstall().  If you don't, someone might have a bad day.
     *
     * @return mixed
     */
    public function onUnInstall()
    {
        $sql = "SET FOREIGN_KEY_CHECKS = 0;
                drop table if exists users;
                drop table if exists sites;
                drop table if exists site_members;
                drop table if exists site_member_roles;
                drop table if exists roles;
                drop table if exists marks;
                drop table if exists metrics;
                drop table if exists page_marks;
                drop table if exists scanned_page;
                drop table if exists scanned_page_links;
                drop table if exists page_metric_grades;
                drop table if exists site_reviews;
                drop table if exists scans;
                drop table if exists site_scan_history;
                drop table if exists site_scan_metric_history;
                SET FOREIGN_KEY_CHECKS = 1;";

        if (!Util::execMultiQuery($sql, true)) {
            return false;
        }

        return true;
    }

    /**
     * Called when the plugin is updated (a newer version exists).
     *
     * @param $previousVersion int The previous installed version
     * @return mixed
     */
    public function onUpdate($previousVersion)
    {
        if ($previousVersion <= 2014032801) {
            $sql = "ALTER TABLE scans MODIFY gpa DECIMAL(5,2);
                    ALTER TABLE sites MODIFY gpa DECIMAL(5,2);
                    ALTER TABLE scans ADD COLUMN pass_fail ENUM('YES', 'NO') NOT NULL DEFAULT 'NO' COMMENT 'Was this scan a pass/fail scan of the site?'";

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2014033101) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2014050101.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
            
            //Update all pages to populate the num_errors and num_notices
            $pages = new \SiteMaster\Core\Auditor\Site\Pages\All();
            foreach ($pages as $page) {
                /**
                 * @var $page \SiteMaster\Core\Auditor\Site\Page
                 */
                if (!$page->isComplete()) {
                    //We only want to update pages that have finished scanning
                    continue;
                }
                
                $errors  = $page->getErrors();
                $notices = $page->getNotices();
                
                $page->num_errors  = $errors->count();
                $page->num_notices = $notices->count();
                
                $page->save();
            }
        }
        
        if ($previousVersion <= 2014050101) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2014052001.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2014052001) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2014061101.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2014061101) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2014062001.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2014062001) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2014091501.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2014091501) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2015011301.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2015011301) {
            $sql = file_get_contents(Util::getRootDir() . "/data/database.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2015020401) {
            $sql = file_get_contents(Util::getRootDir() . "/data/database.sql");
            $sql .= file_get_contents(Util::getRootDir() . "/data/update-2015021701.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2015021701) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2015032301.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2015032301) {
            $sql = file_get_contents(Util::getRootDir() . "/data/database.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2015060301) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2015071301.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2015071301) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2016100701.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2016100701) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2016102401.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2016102401) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2017012601.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }

        if ($previousVersion <= 2017012601) {
            $sql = file_get_contents(Util::getRootDir() . "/data/update-2017040701.sql");

            if (!Util::execMultiQuery($sql, true)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Returns the long name of the plugin
     *
     * @return mixed
     */
    public function getName()
    {
        return 'Core System';
    }

    /**
     * Returns the version of this plugin
     * Follow a YYYYMMDDxx syntax.
     *
     * for example 1118201301
     * would be 11/18/2013 - increment 1
     *
     * @return mixed
     */
    public function getVersion()
    {
        return 2017040701;
    }

    /**
     * Returns a description of the plugin
     *
     * @return mixed
     */
    public function getDescription()
    {
        return 'This is the main plugin for the core system';
    }

    /**
     * Get an array of event listeners
     *
     * @return array
     */
    function getEventListeners()
    {
        $listener = new Listener($this);

        $listeners[] = array(
            'event'    => RoutesCompile::EVENT_NAME,
            'listener' => array($listener, 'onRoutesCompile')
        );

        $listeners[] = array(
            'event'    => MainCompile::EVENT_NAME,
            'listener' => array($listener, 'onNavigationMainCompile')
        );

        $listeners[] = array(
            'event'    => SubCompile::EVENT_NAME,
            'listener' => array($listener, 'onNavigationSubCompile')
        );

        $listeners[] = array(
            'event'    => Events\Navigation\SiteCompile::EVENT_NAME,
            'listener' => array($listener, 'onNavigationSiteCompile')
        );

        $listeners[] = array(
            'event'    => RegisterStyleSheets::EVENT_NAME,
            'listener' => array($listener, 'onThemeRegisterStyleSheets')
        );

        $listeners[] = array(
            'event'    => RegisterScripts::EVENT_NAME,
            'listener' => array($listener, 'onThemeRegisterScripts')
        );

        $listeners[] = array(
            'event'    => Events\User\Search::EVENT_NAME,
            'listener' => array($listener, 'onUserSearch')
        );

        return $listeners;
    }
}