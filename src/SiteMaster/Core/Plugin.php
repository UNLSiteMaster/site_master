<?php

namespace SiteMaster\Core;

use SiteMaster\Core\Events\Navigation\MainCompile;
use SiteMaster\Core\Events\Navigation\SubCompile;
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
        if (!Role::getByRoleName('manager')) {
            Role::createRole('manager');
        }
        
        if (!Role::getByRoleName('developer')) {
            Role::createRole('developer');
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
     * Follow a mmddyyyyxx syntax.
     *
     * for example 1118201301
     * would be 11/18/2013 - increment 1
     *
     * @return mixed
     */
    public function getVersion()
    {
        return 1118201301;
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

        return $listeners;
    }
}