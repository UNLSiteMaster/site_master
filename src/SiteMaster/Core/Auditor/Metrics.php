<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\Site\Member;

class Metrics extends \ArrayIterator
{
    /**
     * Metrics constructor.
     * 
     * @param array $group_name the group to get metrics for
     */
    public function __construct($group_name) {
        $metrics = PluginManager::getManager()->getMetrics($group_name);
        
        parent::__construct($metrics);
    }

    /**
     * Determines if this set of plugins is valid.  A valid set of plugins can not have a total weight above 100 or below 0.
     * 
     * @return bool
     */
    public function validate()
    {
        $total = 0;
        foreach ($this as $metric) {
            $record = $metric->getMetricRecord();
            
            $total += $record->weight;
        }
        
        if ($total > 0 && $total <= 100) {
            return true;
        }
        
        return false;
    }
}