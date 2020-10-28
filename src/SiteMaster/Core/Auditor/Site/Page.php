<?php
namespace SiteMaster\Core\Auditor\Site;

use DB\Record;
use Monolog\Logger;
use SiteMaster\Core\Auditor\Downloader\DownloadException;
use SiteMaster\Core\Auditor\FeatureAnalytics;
use SiteMaster\Core\Auditor\GradingHelper;
use SiteMaster\Core\Auditor\HeadlessRunner;
use SiteMaster\Core\Auditor\Logger\Links;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Parser\HTML5;
use SiteMaster\Core\Auditor\Site\Page\Analytics;
use SiteMaster\Core\Auditor\Site\Page\PageHasFeatureAnalytics;
use SiteMaster\Core\Auditor\Spider;
use SiteMaster\Core\Config;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Downloader\HTMLOnly;
use SiteMaster\Core\Auditor\Logger\Scheduler;
use SiteMaster\Core\Auditor\Logger\PageTitle;
use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\UnexpectedValueException;
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
    public $tries;                 //INT(10), the number of times that the scan has tried to run
    public $num_errors;            //INT, the number of errors found on this page
    public $num_notices;           //INT, the number of notices found on this page
    public $found_with;            //ENUM('SITE_MAP', 'CRAWL') NOT NULL
    public $link_limit_hit;        //ENUM('NO', 'YES') NOT NULL
    public $daemon_name;           //VARCHAR(256) - the name of the daemon that performed the scan

    const STATUS_CREATED  = 'CREATED';
    const STATUS_QUEUED   = 'QUEUED';
    const STATUS_RUNNING  = 'RUNNING';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_ERROR    = 'ERROR';

    const SCAN_TYPE_USER = 'USER';
    const SCAN_TYPE_AUTO = 'AUTO';

    const PRI_AUTO_SITE_SCAN        = 400;
    const PRI_AUTO_PAGE_SCAN        = 300;
    const PRI_USER_SITE_SCAN        = 200;
    const PRI_USER_PAGE_SCAN        = 100;
    const PRI_USER_SINGLE_PAGE_SCAN = 75;
    
    const FOUND_WITH_SITE_MAP = 'SITE_MAP';
    const FOUND_WITH_CRAWL    = 'CRAWL';
    const FOUND_WITH_MANUAL   = 'MANUAL';
    
    const LINK_LIMIT_HIT_NO = 'NO';
    const LIMIT_LIMIT_HIT_YES = 'YES';

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
        //Trim to the max length of the URL as stored in the database
        $sanitized_uri = self::sanitizeURI($uri);
            
        $pages = new Pages\URIForScan(array(
            'scans_id' => $scans_id,
            'uri' => $sanitized_uri,
            'limit' => 1
        ));
        
        foreach ($pages as $page) {
            //Ignore the scheme (http vs https) - only scan the page once
            if (Util::makeAgnostic($page->uri) == Util::makeAgnostic($sanitized_uri)) {
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
     * Get errors for this page
     * 
     * @return Page\Marks\AllForPage
     */
    public function getErrors()
    {
        return new Page\Marks\AllForPage(array(
            'scanned_page_id' => $this->id,
            'mark_type' => 'error',
        ));
    }

    /**
     * Get notices for this page
     * 
     * @return Page\Marks\AllForPage
     */
    public function getNotices()
    {
        return new Page\Marks\AllForPage(array(
            'scanned_page_id' => $this->id,
            'mark_type' => 'notice',
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
     * @param string $found_with the method in which the page was found (crawl, or site_map)
     * @param array $fields an associative array of field names and values to insert
     * @return bool|Page
     */
    public static function createNewPage($scans_id, $sites_id, $uri, $found_with, array $fields = array())
    {
        $sanitized_uri = self::sanitizeURI($uri);
        
        $page = new self();
        $page->status           = self::STATUS_CREATED;
        $page->scan_type        = self::SCAN_TYPE_AUTO;
        $page->percent_grade    = 0;
        $page->point_grade      = 0;
        $page->points_available = 0;
        $page->link_limit_hit   = self::LINK_LIMIT_HIT_NO;
        $page->priority         = self::PRI_AUTO_SITE_SCAN;
        $page->date_created     = Util::epochToDateTime();
        
        $page->synchronizeWithArray($fields);
        $page->scans_id  = $scans_id;
        $page->sites_id  = $sites_id;
        $page->uri       = $sanitized_uri;
        $page->uri_hash  = md5($sanitized_uri, true);
        $page->found_with = $found_with;

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
     * @param bool $priority
     * @return bool True on success, false if it can not be scanned (was already scanned)
     */
    public function scheduleScan($priority = false)
    {
        if ($this->status != self::STATUS_CREATED) {
            //The scan already finished.  Don't scan again.
            return false;
        }
        
        $site = $this->getSite();
        
        if ($priority == false) {
            //Determine the priority
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
        }
        
        $this->markAsQueued($priority);
        
        return true;
    }

    /**
     * Return the RFC3986 version of the URI
     * 
     * @return string
     */
    public function getSanitizedURI()
    {
        return (string)\Guzzle\Http\Url::factory($this->uri);
    }

    /**
     * Scan this page
     *
     * @param string $daemon_name the name of the daemon that is performing the scan
     * @return bool false if the scan is not queued or ready to be scanned
     * @throws \Exception
     */
    public function scan($daemon_name = 'default')
    {
        if ($this->status != self::STATUS_QUEUED) {
            //Looks like it has already been scanned (or has yet to be scheduled).  Don't continue.
            return false;
        }

        $this->markAsRunning($daemon_name);
        
        $scan = $this->getScan();
        $site = $this->getSite();
        
        $spider = new Spider(
            new HTMLOnly($site, $this, $scan),
            new HTML5(),
            array(
                'use_effective_uris' => false,
                'user_agent'         => Config::get('USER_AGENT')
            )
        );

        //Run headless tests against the page (we need to do this here so we can pass the results to the metrics)
        $headless_runner = new HeadlessRunner($daemon_name);
        $headless_results = $headless_runner->run($this->uri);

        if (!array_key_exists('core-links', $headless_results)) {
            // Cannot process so set error message and exit
            $errorMessage = "Bad Headless Runner: Missing 'core_links'";
            $this->setErrorMessage($errorMessage);
            return true;
        }

        Spider::setURIs($headless_results['core-links']);
        
        $spider->addUriFilter('\\SiteMaster\\Core\\Auditor\\Filter\\FileExtension');
        
        $page_title_class = Config::get('PAGE_TITLE_LOGGER');
        if (class_exists($page_title_class)) {
            $page_title_logger = new $page_title_class($this);
        } else {
            $page_title_logger = new PageTitle($this);
        }
        
        if ($this->priority != self::PRI_USER_SINGLE_PAGE_SCAN) {
            //Don't schedule new pages to be scanned if this is a single page scan.
            $spider->addLogger(new Scheduler($spider, $scan, $site));
        }
        
        $spider->addLogger(new Links($spider, $this));
        $spider->addLogger($page_title_logger);
        $spider->addLogger(new Metrics($spider, $scan, $site, $this, $headless_results));
        
        $this->logPageAnalytics($headless_results['core-page-analytics']);

        try {
            $spider->processPage($this->getSanitizedURI(), 1);
        } catch (\Exception $e) {
            if ($e instanceof HTTPConnectionException || $e instanceof DownloadException) {
                //Couldn't get the page, so don't process it.
                //Get the scan before we delete this page
                if (!$scan = $this->getScan()) {
                    //the scan was deleted, probably due to a base url changing to https
                    //Fail early
                    return true;
                }
                
                //Delete this page
                $this->delete();
    
                //Figure out we the site scan is finished.
                if (!$scan->getNextQueuedPage()) {
                    //Could not find any more queued pages to scan.  The scan must be finished.
                    $scan->markAsComplete();
                }

                Util::log(
                    Logger::NOTICE,
                    'Page removed due to exception: ' . $this->id .  ' - ' . $this->uri,
                    array(
                        'exception' => (string)$e,
                    )
                );
                
                //Return early because $this was deleted
                return true;
            } else {
                // Cannot process so set error message and throw error
                $this->setErrorMessage($e->getMessage());
                return true;
            }
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
     * Log page analytics to database
     * 
     * @param array $page_analytics
     */
    function logPageAnalytics(array $page_analytics)
    {
        foreach ($page_analytics as $type=>$data) {
            foreach ($data as $key=>$values) {
                foreach ($values as $value=>$instances) {
                    if ($value == 'null') {
                        $value = null;
                    }
                    
                    if (!$feature = FeatureAnalytics::getByUniqueHash($type, $key, $value)) {
                        $feature = FeatureAnalytics::createNewRecord($type, $key, $value);
                    }
                    
                    PageHasFeatureAnalytics::createNewRecord($feature, $this->id, $instances);
                }
            }
        }
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
            if ($grade->isIncomplete() && $grade->weight > 0) {
                return GradingHelper::GRADE_INCOMPLETE;
            }
        }
        
        $scan = $this->getScan();
        if ($scan->isPassFail()) {
            //Handle the SITE_PASS_FAIL grading method
            if ($percent_grade >= 100) {
                return GradingHelper::GRADE_PASS;
            } else {
                return GradingHelper::GRADE_NO_PASS;
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
        
        if (!$this->save()) {
            return false;
        }
        
        $scan = $this->getScan();
        $scan->markAsQueued();
    }

    /**
     * Mark this page as running
     *
     * @param string $daemon_name the name of the daemon performing the scan
     * @return null
     */
    public function markAsRunning($daemon_name = 'default')
    {
        $scan = $this->getScan();
        if ($scan->status != Scan::STATUS_RUNNING) {
            $scan->markAsRunning();
        }
        
        $this->start_time = Util::epochToDateTime();
        $this->status     = self::STATUS_RUNNING;
        $this->daemon_name = $daemon_name;
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
        //Get errors and notices for later use
        $errors  = $this->getErrors();
        $notices = $this->getNotices();
        
        //Set local properties
        $this->end_time    = Util::epochToDateTime();
        $this->status      = self::STATUS_COMPLETE;
        $this->num_errors  = $errors->count();
        $this->num_notices = $notices->count();
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

        $scan = $this->getScan();
        
        //Figure out we the site scan is finished.
        if (!$scan->getNextQueuedPage()) {
            //Could not find any more queued pages to scan.  The scan must be finished.
            $scan->markAsComplete();
        }
    }

    /**
     * Set the error message for this
     *
     * @param string $errorMessage the error text to save
     * @return null
     */
    public function setErrorMessage($errorMessage)
    {
        if ($this->tries >= 3) {
            //Give up, and mark it as an error
            $this->markAsError($errorMessage);
        } else {
            $this->end_time = Util::epochToDateTime();
            $this->error = $errorMessage;
            $this->rescheduleScan();

            $scan = $this->getScan();

            //Figure out we the site scan is finished.
            if (!$scan->getNextQueuedPage()) {
                //Could not find any more queued pages to scan.  The scan must be finished.
                $scan->markAsComplete();
            }
        }
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
        
        $cloned_page->tries++;
        
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

    /**
     * @return ScanForm
     */
    public function getScanForm()
    {
        return new Page\ScanForm(array('uri'=>$this->uri));
    }

    /**
     * Determine if this scan has completed
     *
     * @return bool
     */
    public function isComplete()
    {
        if (in_array($this->status, array(self::STATUS_COMPLETE, self::STATUS_ERROR))) {
            return true;
        }

        return false;
    }

    /**
     * Get all links found on this page
     * 
     * @return Page\Links\AllForPage
     */
    public function getLinks()
    {
        return new Page\Links\AllForPage(array('scanned_page_id' => $this->id));
    }

    /**
     * Get a list of links that linked to this page for the current scan
     * 
     * @return Page\Links\ForScanAndURL
     */
    public function getLinksToThisPage()
    {
        return new Page\Links\ForScanAndURL(array(
            'scans_id' => $this->scans_id,
            'url'      => $this->uri
        ));
    }

    /**
     * Sanitize a given URL so that it is database safe
     * 
     * @param $uri
     * @return string
     */
    public static function sanitizeURI($uri)
    {
        //Trim it to the max column length of the uri database field
        return mb_substr($uri, 0, 2100);
    }
}
