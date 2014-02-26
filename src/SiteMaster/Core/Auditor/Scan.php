<?php
namespace SiteMaster\Core\Auditor;

use DB\Record;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\Auditor\Site\Pages\AllForScan;
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
    public $date_created;          //DATETIME NOT NULL, the date that this record was created
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
     * @param int $sites_id the site id
     * @param array $fields an associative array of field names and values to insert
     * @return bool|Scan
     */
    public static function createNewScan($sites_id, array $fields = array())
    {
        $scan = new self();
        $scan->gpa        = 0;
        $scan->status     = self::STATUS_CREATED;
        $scan->scan_type  = self::SCAN_TYPE_AUTO;
        $scan->date_created = Util::epochToDateTime();
        
        $scan->synchronizeWithArray($fields);
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
     * Get the previous scan
     * 
     * @return bool
     */
    public function getPreviousScan()
    {
        $pages = new Scans\AllForSite(array(
            'sites_id' => $this->sites_id,
            'not_id' => $this->id,
            'limit' => 1
        ));
        
        if ($pages->count() == 0) {
            return false;
        }
        
        $pages->rewind();
        return $pages->current();
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
     * Get all pages in this scan
     * 
     * @return AllForScan
     */
    public function getPages()
    {
        return new AllForScan(array('scans_id'=>$this->id));
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

    /**
     * Mark this scan as queued
     */
    public function markAsQueued()
    {
        $this->status = self::STATUS_QUEUED;
        $this->save();
    }

    /**
     * Mark this scan as running
     * 
     * @return null
     */
    public function markAsRunning()
    {
        $this->start_time = Util::epochToDateTime();
        $this->status     = self::STATUS_RUNNING;
        $this->save();
    }

    /**
     * Mark this scan as complete
     * 
     * @return null
     */
    public function markAsComplete()
    {
        $this->end_time = Util::epochToDateTime();
        $this->status   = self::STATUS_COMPLETE;
        $this->gpa      = $this->computeGPA();
        if ($this->save()) {
            //remove any extra scans
            $site = $this->getSite();
            $site->cleanScans();
        }
    }

    /**
     * Compute the gpa of this scan
     * 
     * @return float
     */
    public function computeGPA()
    {
        $letter_grades = array();
        foreach ($this->getPages() as $page) {
            $letter_grades[] = $page->letter_grade;
        }
        
        $grading_helper = new GradingHelper();
        return $grading_helper->calculateGPA($letter_grades);
    }

    /**
     * Mark this scan as an error
     * 
     * @param string $error the error message to save
     */
    public function markAsError($error = 'unknown')
    {
        $this->end_time = Util::epochToDateTime();
        $this->status   = self::STATUS_ERROR;
        $this->error    = $error;
        if ($this->save()) {
            //remove any extra scans
            $site = $this->getSite();
            $site->cleanScans();
        }
    }
}
