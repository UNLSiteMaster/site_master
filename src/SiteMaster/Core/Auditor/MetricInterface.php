<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Util;
use SiteMaster\Core\Plugin\PluginManager;

abstract class MetricInterface
{
    public $options;
    public $plugin_name;

    /**
     * @param string $plugin_name (The plugin machine name for this metric)
     * @param array $options an array of options (usually the same options that were passed to the plugin)
     */
    public function __construct($plugin_name, array $options = array())
    {
        $this->plugin_name = $plugin_name;
        $this->options     = $options;
    }
    
    /**
     * Get the human readable name of this metric
     * 
     * @return string The human readable name of the metric
     */
    abstract public function getName();

    /**
     * Get the Machine name of this metric
     * 
     * This is what defines this metric in the database
     * 
     * @return string The unique string name of this metric
     */
    abstract public function getMachineName();

    /**
     * Determine if this metric should be graded as pass-fail
     * 
     * @return bool True if pass-fail, False if normally graded
     */
    abstract public function isPassFail();

    /**
     * Scan a given URI and apply all marks to it.
     * 
     * @return mixed
     */
    abstract public function scan();

    /**
     * Get the metric record for this metric
     * 
     * @return bool|Metric
     */
    public function getMetricRecord()
    {
        if ($metric = $this->getMetricRecord()) {
            //Found the metric, just return it.
            return $metric;
        }

        //Couldn't find the metric.  Install it.
        return Metric::createNewMetric($this->getName(), $this->getMachineName());
    }

    /**
     * Get the plugin class for this metric
     * 
     * @return \SiteMaster\Core\Plugin\PluginInterface
     */
    public function getPlugin()
    {
        return PluginManager::getManager()->getPluginInfo($this->plugin_name);
    }
}