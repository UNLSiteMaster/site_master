<?php
namespace SiteMaster\Core\Plugin;

use SiteMaster\Core\Util;

abstract class PluginInterface
{
    protected $options = array();

    function __construct($options = array())
    {
        $this->options = $options + $this->options;
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

        //Run plugin install logic
        if (!$this->onInstall()) {
            return false;
        }

        //Run any updates.
        if (!$this->onUpdate(false)) {
            return false;
        }

        $plugins = PluginManager::getManager()->getInstalledVersions();

        $plugins[$this->getMachineName()] = $this->getVersion();

        PluginManager::getManager()->updateInstalledPlugins($plugins);
        
        if ($metric = $this->getMetric()) {
            //Create a metric record if we don't have one yet.
            $metric_record = $metric->getMetricRecord();
        }

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

    /**
     * Checks if the plugin is currently installed
     *
     * @return bool
     */
    public function isInstalled()
    {
        $plugins = PluginManager::getManager()->getInstalledVersions();

        if (!isset($plugins[$this->getMachineName()])) {
            return false;
        }

        return true;
    }

    /**
     * Gets the installed version of this plugin
     *
     * @return int
     */
    public function getInstalledVersion()
    {
        $plugins = PluginManager::getManager()->getInstalledVersions();

        if (!isset($plugins[$this->getMachineName()])) {
            return false;
        }

        return $plugins[$this->getMachineName()];
    }

    /**
     * Updates this plugin if needed.
     *
     * @return bool
     */
    protected function update()
    {
        if (!$this->isInstalled()) {
            return false;
        }

        //do we need to update?
        $installedVersion = $this->getInstalledVersion();
        if ((int)$installedVersion >= $this->getVersion()) {
            return false;
        }

        if ($this->onUpdate($installedVersion)) {
            $plugins = PluginManager::getManager()->getInstalledVersions();
            $plugins[$this->getMachineName()] = $this->getVersion();
            PluginManager::getManager()->updateInstalledPlugins($plugins);
        }

        return true;
    }

    /**
     * checks if we need to install, update, or do nothing, then performs that action
     *
     * @return bool
     */
    public function performUpdate()
    {
        $method = $this->getUpdateMethod();
        return $this->$method();
    }

    /**
     * Returns the name of the update action to perform
     * If no updates are required, it returns false
     *
     * @return bool|string
     */
    public function getUpdateMethod()
    {
        if (!$this->isInstalled()) {
            return 'install';
        }

        //do we need to update?
        $installedVersion = $this->getInstalledVersion();
        if ((int)$installedVersion < $this->getVersion()) {
            return 'update';
        }

        //do we need to uninstall?
        if (!in_array($this->getMachineName(), PluginManager::getManager()->getAllPlugins())) {
            return 'uninstall';
        }

        return false;
    }

    /**
     * Get the plugin type
     *
     * @return string - internal or external
     */
    public function getPluginType()
    {
        if (strpos(get_class($this), 'SiteMaster\\Plugins\\') === 0) {
            return 'external';
        }

        return 'internal';
    }

    public function isExternal()
    {
        if ($this->getPluginType() == 'internal') {
            return false;
        }

        return true;
    }

    /**
     * Get the absolute path to this plugin's root directory
     *
     * @return string
     */
    public function getRootDirectory()
    {
        if ($this->isExternal()) {
            return Util::getRootDir() . '/plugins/' . $this->getMachineName();
        }

        return Util::getRootDir();
    }

    /**
     * @return bool|\SiteMaster\Core\Auditor\MetricInterface
     */
    public function getMetric()
    {
        $class = PluginManager::getManager()->getPluginNamespaceFromName($this->getMachineName()) . 'Metric';

        if (!class_exists($class)) {
            return false;
        }
        
        //Metric was found.  add it to the list of metrics.
        return new $class($this->getMachineName(), PluginManager::getManager()->getPluginOptions($this->getMachineName()));
    }
    
    public function initialize()
    {
        //Do important stuff.
    }
}