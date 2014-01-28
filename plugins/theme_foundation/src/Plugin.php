<?php
namespace SiteMaster\Plugins\Theme_Foundation;

use SiteMaster\Core\Plugin\PluginInterface;
use SiteMaster\Core\Events\RegisterTheme;
use SiteMaster\Core\Events\Theme\RegisterScripts;
use SiteMaster\Core\Events\Theme\RegisterStyleSheets;

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
        return 'Example Plugin';
    }

    /**
     * @return mixed|string
     */
    public function getDescription()
    {
        return 'Just a very basic example plugin';
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

        $listener = new Listener($this);

        $listeners[] = array(
            'event'    => RegisterTheme::EVENT_NAME,
            'listener' => array($listener, 'onRegisterTheme')
        );

        $listeners[] = array(
            'event'    => RegisterStyleSheets::EVENT_NAME,
            'listener' => array($listener, 'onThemeRegisterStyleSheets')
        );

        $listeners[] = array(
            'event'    => RegisterScripts::EVENT_NAME,
            'listener' => array($listener, 'onThemeRegisterScripts')
        );

        return $listeners;
    }
}