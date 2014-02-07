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
    public $status;                //ENUM('CREATED', 'QUEUED', 'RUNNING', 'COMPLETE', 'ERROR') NOT NULL default='CREATED'
    public $scan_type;             //ENUM('USER', 'AUTO') NOT NULL default='AUTO'
    public $start_time;            //DATETIME NOT NULL
    public $send_time;             //DATETIME
    public $error;                 //VARCHAR(256)
    
    const STATUS_CREATED  = 'CREATED';
    const STATUS_QUEUED   = 'QUEUED';
    const STATUS_RUNNING  = 'RUNNING';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_ERROR    = 'ERROR';
    
    const SCAN_TYPE_USER = 'USER';
    const SCAN_TYPE_AUTO = 'AUTO';
    
    const PRI_AUTO_SITE_SCAN = 400;
    const PRI_AUTO_PAGE_SCAN = 300;
    const PRI_USER_SITE_SCAN = 200;
    const PRI_USER_PAGE_SCAN = 100;

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
        $scan->gpa        = 0;
        $scan->status     = self::STATUS_CREATED;
        $scan->scan_type  = self::SCAN_TYPE_AUTO;
        $scan->start_time = Util::epochToDateTime();
        
        $scan->synchronizeWithArray($details);
        $scan->sites_id = $sites_id;

        if (!$scan->insert()) {
            return false;
        }

        return $scan;
    }
}
