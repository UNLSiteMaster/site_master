<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Util;

abstract class MetricInterface
{
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
     * Install this metric in the database
     * 
     * @return bool|Metric Returns the installed metric record on success
     */
    public function install()
    {
        if ($metric = Metric::getByMachineName($this->getMachineName())) {
            //Found the metric, just return it.
            return $metric;
        }

        //Couldn't find the metric.  Install it.
        return Metric::createNewMetric($this->getName(), $this->getMachineName());
    }
    
}