<?php
namespace SiteMaster\Plugins\Metric_links;

use SiteMaster\Core\Plugin\PluginInterface;
use SiteMaster\Core\Util;

class Plugin extends PluginInterface
{
    /**
     * @return bool|mixed
     */
    public function onInstall()
    {
        $sql = file_get_contents($this->getRootDirectory() . "/data/database.sql");

        if (!Util::execMultiQuery($sql, true)) {
            return false;
        }
        
        return true;
    }

    /**
     * @return bool|mixed
     */
    public function onUninstall()
    {
        $sql = "SET FOREIGN_KEY_CHECKS = 0;
                drop table if exists metric_links_status;";

        if (!Util::execMultiQuery($sql, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed|string
     */
    public function getName()
    {
        return 'Link Checker Metric';
    }

    /**
     * @return mixed|string
     */
    public function getDescription()
    {
        return 'Link Checker Metric';
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
        return true;
    }

    /**
     * Get an array of event listeners
     *
     * @return array
     */
    function getEventListeners()
    {
        $listeners = array();

        return $listeners;
    }
}