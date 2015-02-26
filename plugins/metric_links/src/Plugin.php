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
        return true;
    }

    /**
     * @return bool|mixed
     */
    public function onUninstall()
    {
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
     * Follow a YYYYMMDDxx syntax.
     *
     * for example 2013111801
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
