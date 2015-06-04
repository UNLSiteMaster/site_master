<?php
namespace SiteMaster\Core\Auditor\Site\History;

use DB\Record;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;

class MetricHistory extends Record
{
    public $id;
    public $site_scan_history_id;
    public $metrics_id;
    public $gpa;

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'site_scan_metric_history';
    }

    /**
     * Create a historical record for a site's GPA
     *
     * @param $site_history_id
     * @param $metrics_id
     * @param $gpa
     * @param array $fields
     * @return bool|SiteHistory
     */
    public static function createNewMetricHistory($site_history_id, $metrics_id, $gpa, array $fields = array())
    {
        $history = new self();
        $history->site_scan_history_id = $site_history_id;
        $history->metrics_id           = $metrics_id;
        $history->gpa                  = $gpa;

        $history->synchronizeWithArray($fields);

        if (!$history->insert()) {
            return false;
        }

        return $history;
    }
}
