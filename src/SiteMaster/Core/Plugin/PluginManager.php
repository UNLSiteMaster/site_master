<?php
namespace SiteMaster\Core\Plugin;

use SiteMaster\Core\Events\GetAuthenticationPlugins;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\Util;

class PluginManager
{
    protected $eventsManager = false;

    protected $options = array(
        'internal_plugins' => array(),
        'external_plugins' => array()
    );
    
    protected $metrics = array();

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
     * @throws \SiteMaster\Core\RuntimeException
     * @return bool | PluginManager
     */
    public static function getManager()
    {
        if (!self::$singleton) {
            throw new RuntimeException("Plugin Manager has not been initialized yet", 500);
        }

        return self::$singleton;
    }

    /**
     * Perform tasks that are usually performed on load such as,
     * set up include paths and initialize plugins.
     */
    public function load()
    {
        $this->initializeIncludePaths();
        
        $this->initializeComposerAutoLoads();

        $this->initializePlugins($this->getInstalledPlugins());
        
        $this->initializeMetrics();
    }

    /**
     * Initialize the singleton
     *
     * @param $eventsManager
     * @param array $options
     * @throws \SiteMaster\Core\RuntimeException
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
            .dirname(dirname(dirname(dirname(__DIR__)))).'/plugins'
        );

        //Include plugin vendor directories
        foreach ($this->getInstalledPlugins() as $name=>$plugin) {
            set_include_path(
                implode(PATH_SEPARATOR, array(get_include_path())) . PATH_SEPARATOR
                .dirname(dirname(dirname(dirname(__DIR__)))).'/plugins/' . $name . '/vendor'
            );
        }
    }

    /**
     * Set up include paths for plugins and libraries
     */
    protected function initializeComposerAutoLoads()
    {
        //Include plugin vendor directories
        foreach ($this->getInstalledPlugins() as $name=>$plugin) {
            $file =  $plugin->getRootDirectory() . '/vendor/autoload.php';
            if (file_exists($file)) {
                include_once $file;
            }
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
    
    public function initializeMetrics()
    {
        foreach ($this->getAllPlugins() as $plugin_name) {
            //The metric should be defined as PLUGIN\Metric
            $class = $this->getPluginNamespaceFromName($plugin_name);
            $class .= 'Metric';
            
            if (class_exists($class)) {
                //Metric was found.  add it to the list of metrics.
                $metric = new $class($plugin_name, $this->getPluginOptions($plugin_name));
                $this->metrics[$metric->getMachineName()] = $metric;
            }
        }
    }
    
    public function getMetrics()
    {
        return $this->metrics;
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
     * Get options for a given plugin
     * 
     * @param $name - the machine name of the plugin
     * @return array
     */
    public function getPluginOptions($name)
    {
        if (isset($this->options['internal_plugins'][$name])) {
            return $this->options['internal_plugins'][$name];
        } else if (isset($this->options['external_plugins'][$name])) {
            return $this->options['external_plugins'][$name];
        }
        
        return array();
    }

    /**
     * @param $name
     * @param array $options
     * @return \SiteMaster\Core\Plugin\PluginInterface
     */
    public function getPluginInfo($name, $options = array()) {
        $class = $this->getPluginNamespaceFromName($name) . 'Plugin';

        //make sure that the passed options are an array
        $options = (array)$options;
        
        $options = $options + $this->getPluginOptions($name);
        
        //Return the plugin class
        return new $class($options);
    }

    /**
     * Get the registered auth plugins
     * 
     * @return array
     */
    public function getAuthPlugins()
    {
        $authPlugins = PluginManager::getManager()->dispatchEvent(
            GetAuthenticationPlugins::EVENT_NAME,
            new GetAuthenticationPlugins()
        );
        
        return $authPlugins->getPlugins();
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