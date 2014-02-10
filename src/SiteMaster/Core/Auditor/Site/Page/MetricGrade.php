<?php
namespace SiteMaster\Core\Auditor\Site\Page;

use DB\Record;
use SiteMaster\Core\Registry\Site\Member;

class MetricGrade extends Record
{
    public $id;                      //int required
    public $metrics_id;              //fk for metrics.id NOT NULL
    public $scanned_page_id;         //fk for scanned_page.id NOT NULL
    public $grade;                   //DECIMAL(2,2) NOT NULL DEFAULT=0
    public $changes_since_last_scan; //INT NOT NULL DEFAULT=0
    public $pass_fail;               //ENUM('YES', 'NO') NOT NULL default='NO'
    public $incomplete;              //ENUM('YES', 'NO') NOT NULL DEFAULT='NO'.  'YES' if the metric was unable to complete
    public $letter_grade;            //VARCHAR(2) for historic tracking of the letter grade in case the scale changes

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'page_metric_grades';
    }

    /**
     * Get a Page Metric grade by the page ID and metric ID
     *
     * @param $metric_id
     * @param $scanned_page_id
     * @return bool|MetricGrade
     */
    public static function getByMetricIDAndScannedPageID($metric_id, $scanned_page_id)
    {
        return self::getByAnyField(__CLASS__, 'metric_id', $metric_id, 'scanned_page_id=' . (int)$scanned_page_id);
    }

    /**
     * Create a new Page Metric Grade
     *
     * @param $metrics_id
     * @param $scanned_page_id
     * @param array $fields
     * @return bool|MetricGrade
     */
    public static function CreateNewPageMetricGrade($metrics_id, $scanned_page_id, array $fields = array())
    {
        $metric_grade = new self();
        $metric_grade->pass_fail = 'NO';
        $metric_grade->incomplete = 'NO';
        $metric_grade->grade = 0;
        $metric_grade->changes_since_last_scan = 0;

        $metric_grade->synchronizeWithArray($fields);
        $metric_grade->metrics_id      = $metrics_id;
        $metric_grade->scanned_page_id = $scanned_page_id;

        if (!$metric_grade->insert()) {
            return false;
        }

        return $metric_grade;
    }
}
