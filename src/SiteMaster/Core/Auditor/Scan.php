<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Util;

class Scan extends Record
{
    public $id;                    //int required
    public $sites_id;              //fk for sites.id
    public $gpa;                   //double(2,2) NOT NULL default=0
    public $status;                //ENUM('CREATED', 'QUEUED', 'RUNNING', 'COMPLETE', 'ERROR') NOT NULL default='CREATED'
    public $scan_type;             //ENUM('USER', 'AUTO') NOT NULL default='AUTO'
    public $start_time;            //DATETIME NOT NULL
    public $end_time;             //DATETIME
    public $error;                 //VARCHAR(256)
    
    const STATUS_CREATED  = 'CREATED';
    const STATUS_QUEUED   = 'QUEUED';
    const STATUS_RUNNING  = 'RUNNING';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_ERROR    = 'ERROR';
    
    const SCAN_TYPE_USER = 'USER';
    const SCAN_TYPE_AUTO = 'AUTO';

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

    /**
     * Get the site for this scan
     * 
     * @return bool|\SiteMaster\Core\Registry\Site
     */
    public function getSite()
    {
        return Site::getByID($this->sites_id);
    }

    /**
     * Get the next page in the queue for this scan
     * 
     * @return bool|\SiteMaster\Core\Auditor\Site\Page
     */
    public function getNextQueuedPage()
    {
        $queue = new Queued(array('scans_id'=>$this->id));

        if (!$queue->count()) {
            return false;
        }

        $queue->rewind();
        return $queue->current();
    }

    /**
     * Schedule a scan for this site
     * (Schedule the base_url) for this scan.
     * 
     * @return bool|int - false on fail, the job id on success
     */
    public function scheduleScan()
    {
        if ($this->status !== self::STATUS_CREATED) {
            //Looks like this has already been ran, or queued.
            return false;
        }
        
        $site = $this->getSite();
        $page_scan = Page::createNewPage($this->id, $this->sites_id, $site->base_url);
        return $page_scan->scheduleScan();
    }

    public function markAsQueued()
    {
        $this->status = self::STATUS_QUEUED;
        $this->save();
    }
    
    public function markAsRunning()
    {
        $this->start_time = Util::epochToDateTime();
        $this->status     = self::STATUS_RUNNING;
        $this->save();
    }

    /**
     * Mark this scan as complete
     */
    public function markAsComplete()
    {
        $this->end_time = Util::epochToDateTime();
        $this->status   = self::STATUS_COMPLETE;
        $this->save();
    }
    
    public function markAsError($error = 'unknown')
    {
        $this->end_time = Util::epochToDateTime();
        $this->status   = self::STATUS_ERROR;
        $this->save();
    }
}
