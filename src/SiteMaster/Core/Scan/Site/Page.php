<?php
namespace SiteMaster\Core\Scan\Site;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Util;
use SiteMaster\Core\RuntimeException;

class Page extends Record
{
    public $id;                    //int required
    public $scans_id;              //fk for scans.id NOT NULL
    public $sites_id;              //fk for sites_id NOT NULL
    public $uri;                   //URI VARCHAR(256) NOT NULL
    public $status;                //ENUM('CREATED', 'QUEUED', 'RUNNING', 'COMPLETE', 'ERROR') NOT NULL default='CREATED'
    public $scan_type;             //ENUM('USER', 'AUTO') NOT NULL default='AUTO'
    public $grade;                 //DOUBLE(2,2) NOT NULL default=0
    public $start_time;            //DATETIME NOT NULL
    public $end_time;              //DATETIME
    public $title;                 //VARCHAR(256)
    public $letter_grade;          //VARCHAR(2)
    public $error;                 //VARCHAR(256)
    public $job_id;                //INT, the job's id in the queue system

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
        return 'scanned_page';
    }

    /**
     * Get a page by its scan id and uri
     *
     * @param $scans_id
     * @param $uri
     * @internal param $base_url
     * @return bool|Page
     */
    public static function getByScanIDAndURI($scans_id, $uri)
    {
        return self::getByAnyField(__CLASS__, 'uri', $uri, 'scans_id=' . (int)$scans_id);
    }

    /**
     * Create a new page
     *
     * @param $scans_id
     * @param $sites_id
     * @param $uri
     * @param array $fields
     * @return bool|Page
     */
    public static function createNewPage($scans_id, $sites_id, $uri, array $fields = array())
    {
        $page = new self();
        $page->status        = self::STATUS_CREATED;
        $page->scan_type     = self::SCAN_TYPE_AUTO;
        $page->grade         = 0;
        $page->start_time    = Util::epochToDateTime();
        
        $page->synchronizeWithArray($fields);
        $page->scans_id = $scans_id;
        $page->sites_id = $sites_id;
        $page->uri      = $uri;

        if (!$page->insert()) {
            return false;
        }

        return $page;
    }

    /**
     * Schedule a scan of this page.
     * 
     * @param bool $crawl - true if we should continue crawling, false if we should only scan this page.
     * @return bool|int
     * @throws \SiteMaster\Core\RuntimeException
     */
    public function scheduleScan($crawl = true)
    {
        if ($this->status != self::STATUS_CREATED) {
            //The scan already finished.  Don't scan again.
            return false;
        }
        
        $pheanstalk = new \Pheanstalk_Pheanstalk('0.0.0.0');
        
        if ($pheanstalk->getConnection()->isServiceListening()) {
            throw new RuntimeException('Unable to connect to the queue. Scan for scanned_pages.' . $this->id . ' failed');
        }

        $data = array(
            'controller' => 'scan-page',
            'data' => array(
                'uri'             => $this->uri,
                'scanned_page_id' => $this->id,
                'crawl'           => $crawl
            )
        );

        if (!$job_id = $pheanstalk->useTube('site_master')->put(json_encode($data))) {
            throw new RuntimeException('Unable to schedule a job for scanned_pages.' . $this->id);
        }
        
        $this->job_id = $job_id;
        $this->status = self::STATUS_QUEUED;
        
        return $this->job_id;
    }
    
    public function scan()
    {
        
    }
}
