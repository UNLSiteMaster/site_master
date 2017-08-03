<?php
namespace SiteMaster\Core\Auditor\Group\History;

use DB\Record;
use SiteMaster\Core\Util;

class GroupHistory extends Record
{
    public $id;
    public $group_name;
    public $gpa;
    public $date_created;
    public $total_pages;

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'group_scan_history';
    }

    /**
     * Create a historical record for a site's GPA
     *
     * @param $group_name
     * @param $gpa
     * @param $total_pages
     * @param array $fields
     * @return bool|GroupHistory
     */
    public static function createNewGroupHistory($group_name, $gpa, $total_pages, array $fields = array())
    {
        $history = new self();
        $history->group_name   = $group_name;
        $history->date_created = Util::epochToDateTime();
        $history->gpa          = $gpa;
        $history->total_pages  = $total_pages;

        $history->synchronizeWithArray($fields);

        if (!$history->insert()) {
            return false;
        }

        return $history;
    }

    /**
     * @return MetricHistoryList\ForGroupHistory
     */
    public function getMetricHistory()
    {
        return new MetricHistoryList\ForGroupHistory(array('group_scan_history_id'=>$this->id));
    }
}
