<?php
namespace SiteMaster\Core\Scan;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;

class Scan extends Record
{
    public $id;                    //int required
    public $sites_id;              //fk for sites.id
    public $gpa;                   //double(2,2) NOT NULL default=0
    public $scan_finished;         //ENUM('YES', 'NO') NOT NULL default='NO'
    public $start_time;            //DATETIME NOT NULL
    public $send_time;             //DATETIME

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'scans';
    }

    /**
     * Create a new Scan
     *
     * @param $sites_id
     * @param array $details
     * @return bool|Scan
     */
    public static function createNewScan($sites_id, array $details = array())
    {
        $scan = new self();
        $scan->gpa = 0;
        $scan->scan_finished = 'NO';
        $scan->start_time = Util::epochToDateTime();
        $scan->synchronizeWithArray($details);
        $scan->sites_id = $sites_id;

        if (!$scan->insert()) {
            return false;
        }

        return $scan;
    }
}
