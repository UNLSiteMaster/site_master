<?php
namespace SiteMaster\Core\Auditor\Group\History;

use DB\Record;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Registry\Site\Member;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;

class MetricHistory extends Record
{
    public $id;
    public $group_scan_history_id;
    public $metrics_id;
    public $gpa;

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'group_scan_metric_history';
    }

    /**
     * Create a historical record for a group's GPA
     *
     * @param $group_history_id
     * @param $metrics_id
     * @param $gpa
     * @param array $fields
     * @return bool|MetricHistory
     */
    public static function createNewMetricHistory($group_history_id, $metrics_id, $gpa, array $fields = array())
    {
        $history = new self();
        $history->group_scan_history_id = $group_history_id;
        $history->metrics_id           = $metrics_id;
        $history->gpa                  = $gpa;

        $history->synchronizeWithArray($fields);

        if (!$history->insert()) {
            return false;
        }

        return $history;
    }

    public function getMetric()
    {
        return Metric::getByID($this->metrics_id);
    }
}
