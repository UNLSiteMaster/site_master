<?php
namespace SiteMaster\Core\Auditor\Site\History;

use DB\Record;
use Monolog\Logger;
use SiteMaster\Core\Auditor\Downloader\DownloadException;
use SiteMaster\Core\Auditor\GradingHelper;
use SiteMaster\Core\Auditor\Logger\Links;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Parser\HTML5;
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

class SiteHistory extends Record
{
    public $id;
    public $sites_id;
    public $gpa;
    public $date_created;
    public $total_pages;
    
    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_scan_history';
    }

    /**
     * Create a historical record for a site's GPA
     *
     * @param Scan $scan
     * @param $gpa
     * @param $total_pages
     * @param array $fields
     * @return bool|SiteHistory
     */
    public static function createNewSiteHistory(Scan $scan, $gpa, $total_pages, array $fields = array())
    {
        $history = new self();
        $history->sites_id     = $scan->sites_id;
        $history->date_created = Util::epochToDateTime();
        $history->gpa          = $gpa;
        $history->total_pages  = $total_pages;

        $history->synchronizeWithArray($fields);

        if (!$history->insert()) {
            return false;
        }

        foreach ($scan->getMetricGPAs() as $metric_id=>$gpa) {
            MetricHistory::createNewMetricHistory($history->id, $metric_id, $gpa);
        }

        return $history;
    }
}
