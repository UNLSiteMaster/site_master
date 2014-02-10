<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;

class Metric extends Record
{
    public $id;             //int required
    public $name;           //VARCHAR(128) NOT NULL, human readable name
    public $machine_name;   //VARCHAR(64) NOT NULL, machine readable name
    public $weight;         //DOUBLE(2,2) NOT NULL default=0, % of total page grade
    public $pass_fail;      //ENUM('YES', 'NO') NOT NULL default='NO'

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
     * @param $name
     * @param $machine_name
     * @param array $fields
     * @return bool|Scan
     */
    public static function createNewMetric($name, $machine_name, array $fields = array())
    {
        $metric = new self();
        $metric->pass_fail = 'NO';
        $metric->weight = 0;
        $metric->synchronizeWithArray($fields);
        
        $metric->name = $name;
        $metric->machine_name = $machine_name;

        if (!$metric->insert()) {
            return false;
        }

        return $metric;
    }
}
