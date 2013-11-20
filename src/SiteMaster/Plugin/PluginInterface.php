<?php
namespace SiteMaster\Plugin;

use SiteMaster\Plugin\PluginManager;

abstract class PluginInterface
{
    protected $options = array();

    function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * Called when a plugin is installed.  Add sql changes and other logic here.
     *
     * @return mixed
     */
    abstract public function onInstall();

    /**
     * Please undo whatever you did in onInstall().  If you don't, someone might have a bad day.
     *
     * @return mixed
     */
    abstract public function onUnInstall();

    /**
     * Called when the plugin is updated (a newer version exists).
     *
     * @param $previousVersion int The previous installed version
     * @return mixed
     */
    abstract public function onUpdate($previousVersion);

    /**
     * Returns the long name of the plugin
     *
     * @return mixed
     */
    abstract public function getName();

    /**
     * Returns the version of this plugin
     * Follow a mmddyyyyxx syntax.
     *
     * for example 1118201301
     * would be 11/18/2013 - increment 1
     *
     * @return mixed
     */
    abstract public function getVersion();

    /**
     * Returns a description of the plugin
     *
     * @return mixed
     */
    abstract public function getDescription();


    /**
     * Get an array of event listeners
     *
     * @return array
     */
    abstract function getEventListeners();

    public function getMachineName()
    {
        return PluginManager::getManager()->getPluginNameFromClass(get_called_class());
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Install this plugin
     *
     * @return bool|mixed
     */
    protected function install()
    {
        //is it already installed?
        if ($this->isInstalled()) {
            return false;
        }

        if (!$this->onInstall()) {
            return false;
        }

        $plugins = PluginManager::getManager()->getInstalledVersions();

        $plugins[$this->getMachineName()] = $this->getVersion();

        PluginManager::getManager()->updateInstalledPlugins($plugins);

        return true;
    }

    /**
     * Uninstall this plugin
     *
     * @return bool|mixed
     */
    public function uninstall()
    {
        if (!$this->onUnInstall()) {
            return false;
        }

        $plugins = PluginManager::getManager()->getInstalledVersions();

        unset($plugins[$this->getMachineName()]);

        PluginManager::getManager()->updateInstalledPlugins($plugins);

        return true;
    }

    public function isInstalled()
    {
        $plugins = PluginManager::getManager()->getInstalledVersions();

        if (!isset($plugins[$this->getMachineName()])) {
            return false;
        }

        return true;
    }

    public function getInstalledVersion()
    {
        $plugins = PluginManager::getManager()->getInstalledVersions();

        if (!isset($plugins[$this->getMachineName()])) {
            return false;
        }

        return $plugins[$this->getMachineName()];
    }

    protected function update()
    {
        if (!$this->isInstalled()) {
            return false;
        }

        //do we need to update?
        $installedVersion = $this->getInstalledVersion();
        if ($installedVersion >= $this->getVersion()) {
            return false;
        }

        if ($this->onUpdate($installedVersion)) {
            $plugins = PluginManager::getManager()->getInstalledVersions();
            $plugins[$this->getMachineName()] = $this->getVersion();
            PluginManager::getManager()->updateInstalledPlugins($plugins);
        }

        return true;
    }

    public function preformUpdate()
    {
        $method = $this->getUpdateMethod();
        return $this->$method();
    }

    public function getUpdateMethod()
    {
        if (!$this->isInstalled()) {
            return 'install';
        }

        //do we need to update?
        $installedVersion = $this->getInstalledVersion();
        if ($installedVersion < $this->getVersion()) {
            return 'update';
        }

        if ($this->onUpdate($installedVersion)) {
            $plugins = PluginManager::getManager()->getInstalledVersions();
            $plugins[$this->getMachineName()] = $this->getVersion();
            PluginManager::getManager()->updateInstalledPlugins($plugins);
        }

        return false;
    }
}