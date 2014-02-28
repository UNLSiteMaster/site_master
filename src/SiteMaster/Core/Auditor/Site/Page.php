<?php
namespace SiteMaster\Core\Auditor\Site;

use DB\Record;
use Monolog\Logger;
use SiteMaster\Core\Auditor\GradingHelper;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Config;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Downloader\HTMLOnly;
use SiteMaster\Core\Auditor\Logger\Scheduler;
use SiteMaster\Core\Auditor\Logger\PageTitle;
use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Util;
use SiteMaster\Core\HTTPConnectionException;

class Page extends Record
{
    public $id;                    //int required
    public $scans_id;              //fk for scans.id NOT NULL
    public $sites_id;              //fk for sites_id NOT NULL
    public $uri;                   //VARCHAR(2100) NOT NULL
    public $uri_hash;              //BINARY(16) NOT NULL, the raw md5 of the uri, for indexing
    public $status;                //ENUM('CREATED', 'QUEUED', 'RUNNING', 'COMPLETE', 'ERROR') NOT NULL default='CREATED'
    public $scan_type;             //ENUM('USER', 'AUTO') NOT NULL default='AUTO'
    public $percent_grade;         //DOUBLE(5,2) NOT NULL default=0
    public $points_available;      //DOUBLE(5,2) NOT NULL default=0
    public $point_grade;           //DOUBLE(5,2) NOT NULL default=0 
    public $priority;              //INT NOT NULL default=300, the priority for this job.  0 is the most urgent
    public $date_created;          //DATETIME NOT NULL, the date that this record was created
    public $start_time;            //DATETIME NOT NULL
    public $end_time;              //DATETIME
    public $title;                 //VARCHAR(256)
    public $letter_grade;          //VARCHAR(2)
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
        return 'scanned_page';
    }

    /**
     * Get the newest page by its scan id and uri
     *
     * @param int $scans_id the id of the scan
     * @param string $uri the uri of the page
     * @return bool|Page
     */
    public static function getByScanIDAndURI($scans_id, $uri)
    {
        $pages = new Pages\URIForScan(array(
            'scans_id' => $scans_id,
            'uri' => $uri,
            'limit' => 1
        ));
        
        foreach ($pages as $page) {
            if ($page->uri == $uri) {
                return $page;
            }
        }
        
        return false;
    }

    /**
     * Get a metric grade for this page
     * 
     * @param int $metric_id the id of the metric
     * @return bool|Page\MetricGrade
     */
    public function getMetricGrade($metric_id)
    {
        return Page\MetricGrade::getByMetricIDAndScannedPageID($metric_id, $this->id);
    }

    /**
     * Get the previous page scan for the current uri
     * 
     * @return bool|Page
     */
    public function getPreviousScan()
    {
        $pages = new Pages\WithURI(array(
            'uri' => $this->uri,
            'not_id' => $this->id,
            'limit' => 1
        ));

        foreach ($pages as $page) {
            if ($page->uri == $this->uri) {
                return $page;
            }
        }

        return false;
    }

    /**
     * Get the marks for a given metric
     * 
     * @param int $metrics_id the metric id to get marks for
     * @return Page\Marks\AllForPageMetric
     */
    public function getMarks($metrics_id)
    {
        return new Page\Marks\AllForPageMetric(array(
            'scanned_page_id' => $this->id,
            'metrics_id' => $metrics_id
        ));
    }

    /**
     * Get all of the metric grades for this page
     * 
     * @return Page\MetricGrades\AllForPage
     */
    public function getMetricGrades()
    {
        return new Page\MetricGrades\AllForPage(array(
            'scanned_page_id' => $this->id,
        ));
    }

    /**
     * Create a new page
     *
     * @param int $scans_id the scan id that this page belongs to
     * @param int $sites_id the site id that this page belongs to
     * @param string $uri the uri of the page
     * @param array $fields an associative array of field names and values to insert
     * @return bool|Page
     */
    public static function createNewPage($scans_id, $sites_id, $uri, array $fields = array())
    {
        $page = new self();
        $page->status           = self::STATUS_CREATED;
        $page->scan_type        = self::SCAN_TYPE_AUTO;
        $page->percent_grade    = 0;
        $page->point_grade      = 0;
        $page->points_available = 0;
        $page->priority         = self::PRI_AUTO_SITE_SCAN;
        $page->date_created     = Util::epochToDateTime();
        
        $page->synchronizeWithArray($fields);
        $page->scans_id = $scans_id;
        $page->sites_id = $sites_id;
        $page->uri      = $uri;
        $page->uri_hash = md5($uri, true);

        if (!$page->insert()) {
            return false;
        }

        return $page;
    }

    /**
     * Get the scan record for this page
     * 
     * @return false|Scan
     */
    public function getScan()
    {
        return Scan::getByID($this->scans_id);
    }

    /**
     * Get the site record for this page
     * 
     * @return false|\SiteMaster\Core\Registry\Site
     */
    public function getSite()
    {
        return Site::getByID($this->sites_id);
    }

    /**
     * Schedule a scan of this page.
     *
     * @return bool True on success, false if it can not be scanned (was already scanned)
     */
    public function scheduleScan()
    {
        if ($this->status != self::STATUS_CREATED) {
            //The scan already finished.  Don't scan again.
            return false;
        }
        
        $site = $this->getSite();
        
        $priority_site = self::PRI_AUTO_SITE_SCAN;
        $priority_page = self::PRI_AUTO_PAGE_SCAN;
        
        if ($this->scan_type == 'USER') {
            $priority_site = self::PRI_USER_SITE_SCAN;
            $priority_page = self::PRI_USER_PAGE_SCAN;
        }
        
        //Set the priority
        $priority = $priority_page;
        if ($this->uri == $site->base_url) {
            $priority = $priority_site;
        }
        
        $this->markAsQueued($priority);
        
        return true;
    }

    /**
     * Scan this page
     * 
     * @return bool false if the scan is not queued or ready to be scanned
     */
    public function scan()
    {
        if ($this->status != self::STATUS_QUEUED) {
            //Looks like it has already been scanned (or has yet to be scheduled).  Don't continue.
            return false;
        }

        $this->markAsRunning();
        
        $scan = $this->getScan();
        $site = $this->getSite();
        
        $spider = new \Spider(
            new HTMLOnly(),
            new \Spider_Parser()
        );
        
        $spider->addUriFilter('\\SiteMaster\\Core\\Auditor\\Filter\\FileExtension');
        
        $page_title_class = Config::get('PAGE_TITLE_LOGGER');
        if (class_exists($page_title_class)) {
            $page_title_logger = new $page_title_class($this);
        } else {
            $page_title_logger = new PageTitle($this);
        }
        
        $spider->addLogger(new Scheduler($spider, $scan, $site));
        $spider->addLogger($page_title_logger);
        $spider->addLogger(new Metrics($spider, $scan, $site, $this));

        try {
            $spider->processPage($this->uri, 1);
        } catch (HTTPConnectionException $e) {
            //Couldn't get the page, so don't process it.
            return $this->delete();
        }
        
        //Ensure that this record is up to date.
        $this->reload();
        
        //Grade
        $this->grade();
        
        //Complete
        $this->markAsComplete();
        
        return true;
    }

    /**
     * Grade this page based on the page metric grades
     * 
     * @return bool true on success
     */
    public function grade()
    {
        $metric_grades = $this->getMetricGrades();
        $this->points_available = $this->computeAvailablePoints($metric_grades);
        $this->point_grade = $this->computePointGrade($metric_grades);
        $this->percent_grade = $this->computePercentGrade($this->point_grade, $this->points_available);
        $this->letter_grade = $this->computeLetterGrade($metric_grades, $this->percent_grade);
        
        return $this->save();
    }

    /**
     * Compute the total available points for this page
     * 
     * @param Page\MetricGrades\AllForPage $metric_grades
     * @return int the available points
     */
    public function computeAvailablePoints(Page\MetricGrades\AllForPage $metric_grades)
    {
        $total_available = 0;
        foreach ($metric_grades as $grade) {
            $total_available += $grade->weight;
        }
        
        return $total_available;
    }

    /**
     * Compute the point grade for this page
     * 
     * @param Page\MetricGrades\AllForPage $metric_grades
     * @return int the point grade of the page
     */
    public function computePointGrade(Page\MetricGrades\AllForPage $metric_grades)
    {
        $total = 0;
        foreach ($metric_grades as $grade) {
            $total += $grade->weighted_grade;
        }

        return $total;
    }

    /**
     * @param double $total_earned the total earned points
     * @param double $total_available the total available points
     * @return double the percent grade
     */
    public function computePercentGrade($total_earned, $total_available)
    {
        if ($total_available == 0) {
            return 0;
        }
        
        return round(($total_earned/$total_available)*100, 2);
    }

    /**
     * Compute the letter grade for this page.
     * 
     * This will also check metric grades to see if any were incomplete, and change the letter grade accordingly
     * 
     * @param Page\MetricGrades\AllForPage $metric_grades the list of metric grades for this page
     * @param $percent_grade double percent grade for this page
     * @return string the letter grade
     */
    public function computeLetterGrade(Page\MetricGrades\AllForPage $metric_grades, $percent_grade)
    {
        foreach ($metric_grades as $grade) {
            if ($grade->isIncomplete()) {
                return GradingHelper::GRADE_INCOMPLETE;
            }
        }
        
        $grade_helper = new GradingHelper();
        
        return $grade_helper->convertPercentToLetterGrade($percent_grade);
    }

    /**
     * Mark this page as queued
     *
     * @param int $priority the priority of the scan for the queue
     * @return null
     */
    public function markAsQueued($priority)
    {
        $this->status   = self::STATUS_QUEUED;
        $this->priority = $priority;
        $this->save();
    }

    /**
     * Mark this page as running
     * 
     * @return null
     */
    public function markAsRunning()
    {
        $scan = $this->getScan();
        if ($scan->status != Scan::STATUS_RUNNING) {
            $scan->markAsRunning();
        }
        
        $this->start_time = Util::epochToDateTime();
        $this->status     = self::STATUS_RUNNING;
        $this->save();
    }

    /**
     * Mark this page as complete
     * If this was the last page in the scan, this will also mark the scan as 'complete'
     * 
     * @return null
     */
    public function markAsComplete()
    {
        $this->end_time = Util::epochToDateTime();
        $this->status   = self::STATUS_COMPLETE;
        $this->save();
        
        $scan = $this->getScan();

        //Figure out we the site scan is finished.
        if (!$scan->getNextQueuedPage()) {
            //Could not find any more queued pages to scan.  The scan must be finished.
            $scan->markAsComplete();
        }
    }

    /**
     * Mark this page as an error
     *
     * @param string $error the error text to save
     * @return null
     */
    public function markAsError($error = 'unknown')
    {
        $this->end_time = Util::epochToDateTime();
        $this->status   = self::STATUS_ERROR;
        $this->error    = $error;
        $this->save();
    }

    /**
     * Create a page mark
     * 
     * @param Mark $mark The Mark object
     * @param array $fields Array of fields such as context, line, col
     * @return bool|Page\Mark
     */
    public function addMark(Mark $mark, array $fields = array())
    {
        return Page\Mark::createNewPageMark($mark->id, $this->id, $mark->point_deduction, $fields);
    }

    /**
     * Reschedule a scan for this page.
     * This will remove this scan, and create a duplicate
     * @return bool|Page the new page
     */
    public function rescheduleScan()
    {
        //copy it for use later
        $cloned_page = clone $this;

        //Delete original page (this should remove all data associated with it)
        $this->delete();

        //Re-save the page as queued
        $cloned_page->status = self::STATUS_QUEUED;
        
        if (!$cloned_page->insert()) {
            return false;
        }
        
        return $cloned_page;
    }

    /**
     * Get the URL for a page
     * 
     * @return string the url for this page
     */
    public function getURL()
    {
        return Config::get('URL') . 'sites/' . $this->sites_id . '/pages/' . $this->id . '/';
    }

    /**
     * Get the title for this page, if we don't have one, return the url
     * 
     * @return mixed
     */
    public function getTitle()
    {
        if (!empty($this->title)) {
            return $this->title;
        }
    }
}
