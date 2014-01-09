<?php
namespace SiteMaster\Plugin;

use SiteMaster\RuntimeException;
use SiteMaster\Util;

class PluginManager
{
    protected $eventsManager = false;

    protected $options = array(
        'internal_plugins' => array(),
        'external_plugins' => array()
    );

    protected static $singleton = false;

    /**
     * Initialize the singleton
     *
     * @param $eventsManager
     * @param array $options
     */
    protected function __construct($eventsManager, $options = array())
    {
        $this->options = $options + $this->options;

        $this->eventsManager = $eventsManager;
    }

    /**
     * Get the plugin manager singleton
     *
     * @throws \SiteMaster\RuntimeException
     * @return bool | \SiteMaster\Plugin\PluginManager
     */
    public static function getManager()
    {
        if (!self::$singleton) {
            throw new RuntimeException("Plugin Manager has not been initialized yet", 500);
        }

        return self::$singleton;
    }

    /**
     * Preform tasks that are usually preformed on load such as,
     * set up include paths and initialize plugins.
     */
    public function load()
    {
        $this->initializeIncludePaths();

        $this->initializePlugins($this->getInstalledPlugins());
    }

    /**
     * Initialize the singleton
     *
     * @param $eventsManager
     * @param array $options
     * @throws \SiteMaster\RuntimeException
     */
    public static function initialize($eventsManager, $options = array())
    {
        if (self::$singleton) {
            throw new RuntimeException("Plugin Manager can only be initialized once", 500);
        }

        self::$singleton = new self($eventsManager, $options);
        self::$singleton->load();
    }

    /**
     * Set up include paths for plugins and libraries
     */
    protected function initializeIncludePaths()
    {
        set_include_path(
            implode(PATH_SEPARATOR, array(get_include_path())) . PATH_SEPARATOR
            .dirname(dirname(dirname(__DIR__))).'/plugins'
        );

        //Include plugin vendor directories
        foreach ($this->getInstalledPlugins() as $name=>$plugin) {
            set_include_path(
                implode(PATH_SEPARATOR, array(get_include_path())) . PATH_SEPARATOR
                .dirname(dirname(dirname(__DIR__))).'/plugins/' . $name . '/vendor'
            );
        }
    }

    public function getInstalledVersions()
    {
        //$all = $this->options['internal_plugins'] + $this->options['external_plugins'];

        $plugins = array();

        if (!$json = @file_get_contents($this->getInstalledPluginsFileName())) {
            return $plugins;
        }

        return json_decode($json, true);
    }

    /**
     * Get a list of installed plugins
     *
     * @return array
     */
    public function getInstalledPlugins()
    {
        //$all = $this->options['internal_plugins'] + $this->options['external_plugins'];

        $plugins = array();

        if (!$json = @file_get_contents($this->getInstalledPluginsFileName())) {
            return $plugins;
        }

        $plugins = $this->getInstalledVersions();

        foreach ($plugins as $name=>$options) {
            //TODO:  Correctly get the options, which will be passed via the config, not the versions file
            $plugins[$name] = $this->getPluginInfo($name, $options);
        }

        return $plugins;
    }

    public function updateInstalledPlugins($plugins)
    {
        return file_put_contents($this->getInstalledPluginsFileName(), json_encode($plugins));
    }

    function getInstalledPluginsFileName()
    {
        return Util::getRootDir() . '/plugins.json';
    }

    /**
     * Initializes a set of plugins.
     *
     * @param array $plugins
     * @internal param $baseNamespace
     */
    protected function initializePlugins(array $plugins)
    {
        foreach ($plugins as $name=>$plugin) {
            foreach ($plugin->getEventListeners() as $listener) {
                $priority = 0;
                if (isset($listener['priority'])) {
                    $priority = $listener['priority'];
                }

                $this->eventsManager->addListener($listener['event'], $listener['listener'], $priority);
            }
        }
    }

    /**
     * Dispatch an event
     *
     * @param $eventName
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @return mixed
     */
    public function dispatchEvent($eventName, \Symfony\Component\EventDispatcher\Event $event = null)
    {
        return $this->eventsManager->dispatch($eventName, $event);
    }

    public function getExternalPlugins()
    {
        return  $this->sanitizePluginNames($this->options['external_plugins']);
    }

    public function getInternalPlugins()
    {
        return $this->sanitizePluginNames($this->options['internal_plugins']);
    }

    public function getAllPlugins()
    {
        return array_merge(
            array_keys($this->getInternalPlugins()),
            array_keys($this->getExternalPlugins())
        );
    }

    protected function sanitizePluginNames($plugins)
    {
        $sanitized = array();

        foreach ($plugins as $name=>$options) {
            $sanitized[strtolower($name)] = $options;
        }

        return $sanitized;
    }

    public function getPluginNameFromClass($class) {
        $parts = explode('\\', $class);

        if (!isset($parts[count($parts)-2])) {
            return false;
        }

        return strtolower($parts[count($parts)-2]);
    }

    public function getPluginNamespaceFromName($name)
    {
        $internalPlugins = $this->getInternalPlugins();

        if (isset($internalPlugins[strtolower($name)])) {
            return '\\SiteMaster\\' . ucfirst(strtolower($name)) . '\\';
        }

        //Its an external plugin
        return '\\SiteMaster\\Plugins\\' . ucfirst(strtolower($name)) . '\\';
    }

    /**
     * @param $name
     * @param array $options
     * @return \SiteMaster\Plugin\PluginInterface
     */
    public function getPluginInfo($name, $options = array()) {
        $class = $this->getPluginNamespaceFromName($name) . 'Plugin';

        //make sure that the passed options are an array
        $options = (array)$options;
        
        //Insert default configured options for the plugin
        if (isset($this->options['internal_plugins'][$name])) {
            $options = $options + $this->options['internal_plugins'][$name];
        } else if (isset($this->options['external_plugins'][$name])) {
            $options = $options + $this->options['external_plugins'][$name];
        }
        
        //Return the plugin class
        return new $class($options);
    }

    public static function autoload($class)
    {
        //try a basic PSR-0 load first

        $file = str_replace(array('_', '\\'), '/', $class).'.php';
        if ($fullpath = stream_resolve_include_path($file)) {
            include $fullpath;
            return true;
        }

        //take of the plugin namespace
        $tmp = str_replace("SiteMaster\\Plugins\\", "", $class, $count);

        //if the plugin namespace wasn't found... don't continue
        if (!$count) {
            return false;
        }

        $parts = explode("\\", $tmp);

        //If there is nothing after the plugin, don't continue.
        if (!$plugin = array_shift($parts)) {
            return false;
        }

        //start the starting directory (plugin/src/) for plugin classes
        $file = strtolower($plugin) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

        //convert the namespace to a path
        $file .=  implode(DIRECTORY_SEPARATOR, $parts).'.php';

        if ($fullpath = stream_resolve_include_path($file)) {
            include $fullpath;
            return true;
        }

        return false;
    }
}