<?php
namespace SiteMaster\Core\Scan\Metric;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

class Mark extends Record
{
    public $id;                    //int required
    public $metric_id;             //fk for metrics.id NOT NULL
    public $machine_name;          //VARCHAR(64) NOT NULL, machine readable name
    public $name;                  //TEXT NOT NULL, human readable name
    public $point_deduction;       //DECIMAL(2,2) NOT NULL default=0, points to take off for the mark
    public $description;           //TEXT, a description of the error
    public $help_text;             //TEXT, a how-to-fix text (will be editable by admin)

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'marks';
    }

    /**
     * Create a new Scan
     *
     * @param $metric_id
     * @param $machine_name
     * @param $name
     * @param array $fields
     * @return bool|Mark
     */
    public static function createNewMark($metric_id, $machine_name, $name, array $fields = array())
    {
        $scan = new self();
        $scan->point_deduction = 0;
        $scan->synchronizeWithArray($fields);
        $scan->metric_id    = $metric_id;
        $scan->machine_name = $machine_name;
        $scan->name         = $name;

        if (!$scan->insert()) {
            return false;
        }

        return $scan;
    }
}
