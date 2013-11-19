<?php
namespace SiteMaster\Plugin;

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
     * Returns the long name of the plugin
     *
     * @return mixed
     */
    abstract public function getName();

    /**
     * Returns a description of the plugin
     *
     * @return mixed
     */
    abstract public function getDescription();

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
    public function install()
    {
        //is it already installed?
        if ($this->isInstalled()) {
            return false;
        }

        $plugin = new Plugin();
        $plugin->name = $this->getMachineName();

        if (!$plugin->save()) {
            return false;
        }

        return $this->onInstall();
    }

    /**
     * Uninstall this plugin
     *
     * @return bool|mixed
     */
    public function uninstall()
    {
        //is it already unInstalled?
        if (!$plugin = Plugin::getByName($this->getMachineName())) {
            return false;
        }

        if (!$plugin->delete()) {
            return false;
        }

        return $this->onUnInstall();
    }

    public function isInstalled()
    {
        if (!$plugin = Plugin::getByName($this->getMachineName())) {
            return false;
        }

        return true;
    }
}