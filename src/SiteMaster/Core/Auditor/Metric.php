<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;

class Metric extends Record
{
    public $id;             //int required
    public $machine_name;   //VARCHAR(64) NOT NULL, machine readable name

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'metrics';
    }

    public static function getByMachineName($machine_name)
    {
        return self::getByAnyField(__CLASS__, 'machine_name', $machine_name);
    }

    /**
     * Create a new Metric
     *
     * @param string $machine_name The machine name that links the db record to a module
     * @param array $fields
     * @return bool|Metric
     */
    public static function createNewMetric($machine_name, array $fields = array())
    {
        $metric = new self();
        $metric->synchronizeWithArray($fields);
        
        $metric->machine_name = $machine_name;

        if (!$metric->insert()) {
            return false;
        }

        return $metric;
    }
}
